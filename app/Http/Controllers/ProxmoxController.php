<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\qemu;
use App\Models\Storage;
use App\Models\node;
use App\Models\cluster;
use App\Models\VirtualMachineHistory;
use App\Services\ProxmoxService2;
use Maatwebsite\Excel\Facades\Excel;


class ProxmoxController extends Controller
{
    //
    protected $proxmoxService;

    public function __construct(ProxmoxService2 $proxmoxService)
    {
        $this->proxmoxService = $proxmoxService;
    }

    public function home()
    {
        $totalClusters = Cluster::count();
        $totalNodes = Node::count();
        $totalQemus = Qemu::count();
        $VMHistory = VirtualMachineHistory::all();
        $totalStorages = Storage::count();

        $totalCPU = Node::sum('maxcpu');
        $totalRAM = Node::sum('maxmem');
        //que la suma no incluya ni al 'Backup-Virt' tampoco  'local' ni 'local-lvm'
        $totalDisk = Storage::where('storage', '!=', 'Backup-Virt')->where('storage', '!=', 'local')->where('storage', '!=', 'local-lvm')->sum('maxdisk');

        //cpuUsagePercentage, Node->cpu es el porcentaje de uso de cpu de cada nodo        
        $cpuUsagePercentage = Node::sum('cpu');
        if($cpuUsagePercentage == 0){
            $cpuUsagePercentage = 0;
        }else{
            $cpuUsagePercentage = $cpuUsagePercentage / $totalCPU * 100;
        }

        //memoryUsagePercentage
        $memoryUsagePercentage = Node::sum('mem');
        if($memoryUsagePercentage == 0){
            $memoryUsagePercentage = 0;
        }else{
        $memoryUsagePercentage = $memoryUsagePercentage / $totalRAM * 100;
        }
        //diskUsagePercentage
        $diskUsagePercentage = Storage::where('storage', '!=', 'Backup-Virt')->where('storage', '!=', 'local')->where('storage', '!=', 'local-lvm')->sum('disk');
        if($diskUsagePercentage == 0){
            $diskUsagePercentage = 0;
        }else{
        $diskUsagePercentage = $diskUsagePercentage / $totalDisk * 100;
        }


        $diskUsagePercentage = round($diskUsagePercentage, 2);

        //qemus status running
        $totalQemusRunning = Qemu::where('status', 'running')->count();
        $totalQemusStopped = Qemu::where('status', 'stopped')->count();


        //historial de maquinas virtuales


        return view('proxmox.home', [
            'totalClusters' => $totalClusters,
            'totalNodes' => $totalNodes,
            'totalQemus' => $totalQemus,
            'totalStorages' => $totalStorages,
            'totalCPU' => $totalCPU,
            'totalRAM' => $totalRAM,
            'totalDisk' => $totalDisk,
            'cpuUsagePercentage' => $cpuUsagePercentage,
            'memoryUsagePercentage' => $memoryUsagePercentage,
            'diskUsagePercentage' => $diskUsagePercentage,
            'totalQemusRunning' => $totalQemusRunning,
            'totalQemusStopped' => $totalQemusStopped
        ]);
    }

    public function getData()
    {
        $this->proxmoxService->processClusterNodes();
        $this->proxmoxService->VMHistory();
        return redirect()->route('proxmox.index');
    }

    public function index()
    {
        $qemus = qemu::all();
        $storages = storage::all();
        $nodes = node::all();
        $clusters = cluster::all();


        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];
        $storageLocalMax = [];

        // Recorre todos los qemus y suma sus sizes por node_id
        foreach ($qemus as $qemu) {
            $nodeId = $qemu->node_id;
            $size = $this->getSizeInBytes($qemu->size);

            if (!isset($sizeSumByNodeId[$nodeId])) {
                $sizeSumByNodeId[$nodeId] = 0;
            }

            $sizeSumByNodeId[$nodeId] += $size;
        }

        foreach ($storages as $storage) {
            $nodeId = $storage->node_id;
            $maxdisk = $storage->maxdisk;
            $storageName = $storage->storage;

            if (!isset($storageLocalMax[$nodeId])) {
                $storageLocalMax[$nodeId] = 0;
            }

            if ($storageName != 'Backup-Virt') {
                $storageLocalMax[$nodeId] += $maxdisk;
            }
        }


        return view('proxmox.show', [
            'qemus' => $qemus,
            'storages' => $storages,
            'nodes' => $nodes,
            'storageLocalMax' => $storageLocalMax,
            'clusters' => $clusters,
            'storageLocal' => $sizeSumByNodeId
        ]);
    }

    private function getSizeInBytes($sizeStr)
    {
        preg_match('/(\d+)(G|T)/', $sizeStr, $matches);
        $size = $matches[1] ?? 0;
        $unit = $matches[2] ?? 'G';

        if ($unit === 'T') {
            return $size * 1024 * 1024 * 1024 * 1024; // Convertir terabytes a bytes
        } else {
            return $size * 1024 * 1024 * 1024; // Convertir gigabytes a bytes
        }
    }


    public function node()
    {
        $nodes = node::all();
        $nodeIds = $nodes->pluck('id_proxmox')->toArray();
        $qemus = Qemu::whereIn('node_id', $nodeIds)->get();
        $storages = Storage::whereIn('node_id', $nodeIds)->get();

        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];
        $storageLocalMax = [];

        // suma maxdisk del storage asociado al node_id y almacenarla en storageLocalMax
        // no sumar "Backup-Virt"
        foreach ($storages as $storage) {
            $nodeId = $storage->node_id;
            $maxdisk = $storage->maxdisk;
            $storageName = $storage->storage;

            if (!isset($storageLocalMax[$nodeId])) {
                $storageLocalMax[$nodeId] = 0;
            }

            if ($storageName != 'Backup-Virt') {
                $storageLocalMax[$nodeId] += $maxdisk;
            }
        }

        // Recorre todos los qemus y suma sus sizes por node_id
        foreach ($qemus as $qemu) {
            $nodeId = $qemu->node_id;
            $size = $this->getSizeInBytes($qemu->size);

            if (!isset($sizeSumByNodeId[$nodeId])) {
                $sizeSumByNodeId[$nodeId] = 0;
            }

            $sizeSumByNodeId[$nodeId] += $size;
        }

        return view(
            'proxmox.node',
            [
                'nodes' => $nodes,
                'qemus' => $qemus,
                'storages' => $storages,
                'storageLocal' => $sizeSumByNodeId,
                'storageLocalMax' => $storageLocalMax
            ]
        );
    }

    public function qemu()
    {
        $qemus = qemu::all();
        return view('proxmox.qemu', ['qemus' => $qemus]);
    }

    public function storage()
    {
        $storages = storage::all();
        return view('proxmox.storage', ['storages' => $storages]);
    }

    public function destroyCluster($name)
    {
        //eliminar cluster y los nodos asociados, y los qemus y los storages
        $cluster = cluster::find($name);
        $nodes = node::where('cluster_name', $name)->get();
        $nodeIds = $nodes->pluck('id_proxmox')->toArray();
        $qemus = Qemu::whereIn('node_id', $nodeIds)->get();
        $storages = Storage::whereIn('node_id', $nodeIds)->get();

        foreach ($qemus as $qemu) {
            $qemu->delete();
        }
        foreach ($storages as $storage) {
            $storage->delete();
        }
        foreach ($nodes as $node) {
            $node->delete();
        }
        $cluster->delete();
        return redirect()->route('proxmox.index');
    }

    public function destroyNode($name)
    {
        //eliminar cluster y los nodos asociados, y los qemus y los storages
        $node = node::find('node/' . $name);
        $qemus = Qemu::where('node_id', $node->id_proxmox)->get();
        $storages = Storage::where('node_id', $node->id_proxmox)->get();

        foreach ($qemus as $qemu) {
            $qemu->delete();
        }
        foreach ($storages as $storage) {
            $storage->delete();
        }
        $node->delete();
        return redirect()->route('proxmox.index');
    }

    public function createCluster()
    {
        return view('proxmox.cluster.create');
    }

    public function storeCluster(Request $request)
    {
        $ip = $request->input('ip');
        $this->proxmoxService->addCluster($ip, $request->input('username'), $request->input('password'));
        return redirect()->route('proxmox.index');
    }

    public function showbyIdCluster($name)
    {
        $cluster = cluster::find($name);
        $nodes = node::where('cluster_name', $name)->get();
        $nodeIds = $nodes->pluck('id_proxmox')->toArray();
        $qemus = Qemu::whereIn('node_id', $nodeIds)->get();
        $storages = Storage::whereIn('node_id', $nodeIds)->get();

        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];
        $storageLocalMax = [];

        // suma maxdisk del storage asociado al node_id y almacenarla en storageLocalMax
        // no sumar "Backup-Virt"
        foreach ($storages as $storage) {
            $nodeId = $storage->node_id;
            $maxdisk = $storage->maxdisk;
            $storageName = $storage->storage;

            if (!isset($storageLocalMax[$nodeId])) {
                $storageLocalMax[$nodeId] = 0;
            }

            if ($storageName != 'Backup-Virt') {
                $storageLocalMax[$nodeId] += $maxdisk;
            }
        }

        // Recorre todos los qemus y suma sus sizes por node_id
        foreach ($qemus as $qemu) {
            $nodeId = $qemu->node_id;
            $size = $this->getSizeInBytes($qemu->size);

            if (!isset($sizeSumByNodeId[$nodeId])) {
                $sizeSumByNodeId[$nodeId] = 0;
            }

            $sizeSumByNodeId[$nodeId] += $size;
        }


        return view(
            'proxmox.cluster.show',
            compact('cluster'),
            [
                'nodes' => $nodes,
                'qemus' => $qemus,
                'storages' => $storages,
                'storageLocal' => $sizeSumByNodeId,
                'storageLocalMax' => $storageLocalMax
            ]
        );
    }

    public function exportQemuCSV()
    {
        $qemus = qemu::all();
        // nombre del cluster obtenido del nodo que pertenece el qemu
        foreach ($qemus as $qemu) {
            $node = node::find($qemu->node_id);
            $qemu->cluster_name = $node->cluster_name;
        }
        //transformar el maxmem de bytes a gigabytes
        foreach ($qemus as $qemu) {
            $qemu->maxmem = ($qemu->maxmem / 1024 / 1024 / 1024)." Gb";
        }
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($qemus, ['id_proxmox', 'name', 'status', 'node_id', 'size', 'disk', 'maxcpu', 'maxmem',  'type', 'cluster_name', 'storageName'])->download();
    }

    public function searchQemu(Request $request)
    {
        $search = $request->get('search');
        $qemus = Qemu::where('name', 'like', '%' . $search . '%')->paginate(100)->appends(['search' => $search]);
        return view('proxmox.qemu', ['qemus' => $qemus]);
    }

    public function searchNode(Request $request)
    {
        $search = $request->get('search');
        $nodes = Node::where('id_proxmox', 'like', '%' . $search . '%')->paginate(100)->appends(['search' => $search]);

        //realizar una funcion en vez de repetir 
        $nodeIds = $nodes->pluck('id_proxmox')->toArray();
        $qemus = Qemu::whereIn('node_id', $nodeIds)->get();
        $storages = Storage::whereIn('node_id', $nodeIds)->get();
        
        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];
        $storageLocalMax = [];

        // suma maxdisk del storage asociado al node_id y almacenarla en storageLocalMax
        // no sumar "Backup-Virt"
        foreach ($storages as $storage) {
            $nodeId = $storage->node_id;
            $maxdisk = $storage->maxdisk;
            $storageName = $storage->storage;

            if (!isset($storageLocalMax[$nodeId])) {
                $storageLocalMax[$nodeId] = 0;
            }

            if ($storageName != 'Backup-Virt') {
                $storageLocalMax[$nodeId] += $maxdisk;
            }
        }

        // Recorre todos los qemus y suma sus sizes por node_id
        foreach ($qemus as $qemu) {
            $nodeId = $qemu->node_id;
            $size = $this->getSizeInBytes($qemu->size);

            if (!isset($sizeSumByNodeId[$nodeId])) {
                $sizeSumByNodeId[$nodeId] = 0;
            }

            $sizeSumByNodeId[$nodeId] += $size;
        }

        return view(
            'proxmox.node',
            [
                'nodes' => $nodes,
                'qemus' => $qemus,
                'storages' => $storages,
                'storageLocal' => $sizeSumByNodeId,
                'storageLocalMax' => $storageLocalMax
            ]
        );
        

            /*  return view('proxmox.node', ['nodes' => $nodes]); */   
    }

    public function searchStorage(Request $request)
    {
        $search = $request->get('search');
        $storages = Storage::where('storage', 'like', '%' . $search . '%')->paginate(100)->appends(['search' => $search]);
        return view('proxmox.storage', ['storages' => $storages]);
    }

    public function showByIdNode($node)
    {
        $node = 'node/'.$node;
        
        $nodes = node::where('id_proxmox', $node)->get();
        foreach ($nodes as $node) {
            $qemus = Qemu::where('node_id', $node->id_proxmox)->get();
            $storages = Storage::where('node_id', $node->id_proxmox)->get();
        }

        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];
        $storageLocalMax = [];

        // suma maxdisk del storage asociado al node_id y almacenarla en storageLocalMax
        // no sumar "Backup-Virt"
        foreach ($storages as $storage) {
            $nodeId = $storage->node_id;
            $maxdisk = $storage->maxdisk;
            $storageName = $storage->storage;

            if (!isset($storageLocalMax[$nodeId])) {
                $storageLocalMax[$nodeId] = 0;
            }

            if ($storageName != 'Backup-Virt') {
                $storageLocalMax[$nodeId] += $maxdisk;
            }
        }

        // Recorre todos los qemus y suma sus sizes por node_id
        foreach ($qemus as $qemu) {
            $nodeId = $qemu->node_id;
            $size = $this->getSizeInBytes($qemu->size);

            if (!isset($sizeSumByNodeId[$nodeId])) {
                $sizeSumByNodeId[$nodeId] = 0;
            }

            $sizeSumByNodeId[$nodeId] += $size;
        }

        return view(
            'proxmox.node.show',
            [
                'nodes' => $nodes,
                'qemus' => $qemus,
                'storages' => $storages,
                'storageLocal' => $sizeSumByNodeId,
                'storageLocalMax' => $storageLocalMax
            ]
        );
    }

    public function showVMHistory()
    {
        $VMHistory = VirtualMachineHistory::all();
        return view('proxmox.history', ['VMHistory' => $VMHistory]);
    }
}
