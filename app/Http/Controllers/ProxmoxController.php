<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\qemu;
use App\Models\Storage;
use App\Models\node;
use App\Models\cluster;
use App\Models\QemuDeleted;
use App\Models\VirtualMachineHistory;
use App\Services\ProxmoxService2;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class ProxmoxController extends Controller
{
    //
    protected $proxmoxService;

    public function __construct(ProxmoxService2 $proxmoxService)
    {
        $this->middleware('auth', ['except' => ['getDataRedirect2']]);
        $this->proxmoxService = $proxmoxService;
    }

    /**
     * Método para obtener los datos necesarios para la vista de inicio.
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        $lastRecords = VirtualMachineHistory::selectRaw('MAX(date) as last_date, cluster_name')
            ->groupBy('cluster_name')
            ->pluck('last_date', 'cluster_name');

        $totals = VirtualMachineHistory::whereIn('cluster_name', array_keys($lastRecords->toArray()))
            ->whereIn('date', $lastRecords->toArray())
            ->selectRaw('SUM(cluster_cpu) as totalCPU, SUM(cluster_memory) as totalRAM, SUM(cluster_disk) as totalDisk, SUM(cluster_qemus) as totalQemus')
            ->first();

        $totalClusters = Cluster::count();
        $totalNodes = Node::count();

        $totalStorages = Storage::count();

        $totalCPU = Node::sum('maxcpu');
        $totalRAM = Node::sum('maxmem');
        //que la suma no incluya ni al 'Backup-Virt' tampoco  'local' ni 'local-lvm'
        $totalDisk = Storage::where('storage', '!=', 'Backup-Virt')->where('storage', '!=', 'local')->where('storage', '!=', 'local-lvm')->sum('maxdisk');

        //cpuUsagePercentage, Node->cpu es el porcentaje de uso de cpu de cada nodo
        $cpuUsagePercentage = Node::sum('cpu');
        if ($cpuUsagePercentage == 0) {
            $cpuUsagePercentage = 0;
        } else {
            $cpuUsagePercentage = $cpuUsagePercentage / $totalCPU * 100;
        }

        //memoryUsagePercentage
        $memoryUsagePercentage = Node::sum('mem');
        if ($memoryUsagePercentage == 0) {
            $memoryUsagePercentage = 0;
        } else {
            $memoryUsagePercentage = $memoryUsagePercentage / $totalRAM * 100;
        }
        //diskUsagePercentage
        $diskUsagePercentage = Storage::where('storage', '!=', 'Backup-Virt')->where('storage', '!=', 'local')->where('storage', '!=', 'local-lvm')->sum('disk');
        if ($diskUsagePercentage == 0) {
            $diskUsagePercentage = 0;
        } else {
            $diskUsagePercentage = $diskUsagePercentage / $totalDisk * 100;
        }

        $diskUsagePercentage = round($diskUsagePercentage, 2);

        //nodos con cluster name null
        $OnlyNodes = Node::where('cluster_name', null)->count();

        return view('proxmox.home', [
            'totalClusters' => $totalClusters,
            'totalNodes' => $totalNodes,
            'totalQemus' => $totals->totalQemus,
            'totalStorages' => $totalStorages,
            'totalCPU' => $totals->totalCPU,
            'totalNodeCpu' => $totalCPU,
            'totalNodeRAM' => $totalRAM,
            'totalRAM' => $totals->totalRAM,
            'totalDisk' => $totals->totalDisk,
            'cpuUsagePercentage' => $cpuUsagePercentage,
            'memoryUsagePercentage' => $memoryUsagePercentage,
            'diskUsagePercentage' => $diskUsagePercentage,
            'OnlyNodes' => $OnlyNodes
        ]);
    }
    /**
     * Recupera datos de Proxmox, procesa los nodos del clúster y el historial de las máquinas virtuales,
     * y redirige a la ruta 'proxmox.index'.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getData()
    {

        try {

            $this->proxmoxService->processClusterNodes();
            $this->proxmoxService->markMissingQemuAsDeleted();
            $this->proxmoxService->VMHistory();
            $this->proxmoxService->resetUpdatedQemuIds();
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    /**
     * Actualiza los datos y redirige a la ruta 'proxmox.index'.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getDataRedirect()
    {
        set_time_limit(60);
        try {

            $this->getData();
            return redirect()->route('proxmox.index');
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }
    /**
     * Actualiza los datos sin la necesidad de iniciar sesion.
     *
     *
     */
    public function getDataRedirect2()
    {
        set_time_limit(60);
        try {

            $this->getData();
            return redirect()->back();
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred.');
        }
    }

    /**
     * Método para obtener la información necesaria en la vista index.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Obtiene todos los registros de la tabla qemu
        $qemus = qemu::all();

        // Obtiene todos los registros de la tabla storage
        $storages = storage::where('storage', '!=', 'local')
            ->where('storage', '!=', 'local-lvm')
            ->where('storage', '!=', 'Backup')
            ->where('storage', '!=', 'Backup-Vicidial')->get();

        $uniqueNames = [];
        $filteredStorages = [];
        $totalUsedDisk = 0;
        $totalMaxDisk = 0;

        foreach ($storages as $storage) {
            if (!in_array($storage->storage, $uniqueNames)) {
                $uniqueNames[] = $storage->storage;

                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial') {

                    $filteredStorages[] = $storage;
                    // Suma al total usado y al tamaño máximo a medida que filtras los storages
                    $totalUsedDisk += $storage->disk;
                    $totalMaxDisk += $storage->maxdisk;
                }
            }
        }

        // Obtiene todos los registros de la tabla node
        $nodes = node::all();

        // Obtiene todos los registros de la tabla cluster
        $clusters = cluster::all();

        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];

        // Inicializa un arreglo para almacenar el máximo de almacenamiento local por node_id
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

        // Recorre todos los storages y suma los maxdisk por node_id, excluyendo el storage 'Backup-Virt'
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
            'storages' => $filteredStorages,
            'nodes' => $nodes,
            'storageLocalMax' => $storageLocalMax,
            'clusters' => $clusters,
            'storageLocal' => $sizeSumByNodeId
        ]);
    }

    /**
     * Convierte una cadena de tamaño en bytes.
     *
     * @param string $sizeStr La cadena de tamaño en formato "número unidad" (por ejemplo, "10G" para 10 gigabytes).
     * @return int El tamaño en bytes.
     */
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

    public function cluster()
    {
        $clusters = cluster::all();
        return view('proxmox.cluster.index', ['clusters' => $clusters]);
    }

    /**
     * Método para obtener información de los nodos, qemus y storages.
     *
     * @return \Illuminate\View\View
     */
    public function node()
    {
        // Obtiene todos los nodos
        $nodes = node::all();

        // Obtiene los IDs de los nodos
        $nodeIds = $nodes->pluck('id_proxmox')->toArray();

        // Obtiene los qemus asociados a los nodos
        $qemus = Qemu::whereIn('node_id', $nodeIds)->get();

        // Obtiene los storages asociados a los nodos
        $storages = Storage::whereIn('node_id', $nodeIds)->get();

        // Inicializa un arreglo para almacenar las sumas de size por node_id
        $sizeSumByNodeId = [];

        // Inicializa un arreglo para almacenar el tamaño máximo de almacenamiento local por node_id
        $storageLocalMax = [];

        // Suma el tamaño máximo del almacenamiento local asociado a cada node_id
        // No se suma el almacenamiento "Backup-Virt"
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

        // Recorre todos los qemus y suma sus tamaños por node_id
        foreach ($qemus as $qemu) {
            $nodeId = $qemu->node_id;
            $size = $this->getSizeInBytes($qemu->size);

            if (!isset($sizeSumByNodeId[$nodeId])) {
                $sizeSumByNodeId[$nodeId] = 0;
            }

            $sizeSumByNodeId[$nodeId] += $size;
        }

        // Retorna la vista con la información obtenida
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

    /**
     * Método para obtener todos los registros de la tabla qemu y mostrarlos en la vista 'proxmox.qemu'.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function qemu()
    {
        $qemus = qemu::all();
        return view('proxmox.qemu', ['qemus' => $qemus]);
    }

    /**
     * Método para obtener todos los registros de la tabla storage y mostrarlos en la vista 'proxmox.storage'.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function storage()
    {
        $storages = storage::where('storage', '!=', 'local')
            ->where('storage', '!=', 'local-lvm')
            ->where('storage', '!=', 'Backup')
            ->where('storage', '!=', 'Backup-Vicidial')->get();

        $uniqueNames = [];
        $filteredStorages = [];
        $totalUsedDisk = 0;
        $totalMaxDisk = 0;

        foreach ($storages as $storage) {
            if (!in_array($storage->storage, $uniqueNames)) {
                $uniqueNames[] = $storage->storage;

                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial') {

                    $filteredStorages[] = $storage;
                    // Suma al total usado y al tamaño máximo a medida que filtras los storages
                    $totalUsedDisk += $storage->disk;
                    $totalMaxDisk += $storage->maxdisk;
                }
            }
        }

        // Pasa los totales calculados a la vista junto con los storages filtrados
        return view('proxmox.storage', [
            'storages' => $storages,
            'filteredStorages' => $filteredStorages,
            'totalUsedDisk' => $totalUsedDisk,
            'totalMaxDisk' => $totalMaxDisk
        ]);
    }


    /**
     * Elimina un clúster y sus nodos, qemus y almacenamientos asociados.
     *
     * @param string $name El nombre del clúster a eliminar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyCluster($name)
    {
        try {
            //eliminar clúster y los nodos asociados, y los qemus y los almacenamientos
            $cluster = cluster::find($name);
            if (!$cluster) {
                throw new \Exception('Cluster no encontrado');
            }
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
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while deleting the cluster.');
        }
    }

    /**
     * Elimina un nodo y todos los qemus y storages asociados.
     *
     * @param string $name El nombre del nodo a eliminar.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyNode($name)
    {
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

    /**
     * Muestra la vista para crear un clúster.
     *
     * @return \Illuminate\View\View
     */
    public function createCluster()
    {
        return view('proxmox.cluster.create');
    }

    /**
     * Almacena un clúster en la base de datos y sus credenciales.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCluster(Request $request)
    {

        try {
            $ip = $request->input('ip');
            $username = $request->input('username');
            $password = $request->input('password');
            $this->proxmoxService->addCluster($ip, $username, $password);
            return redirect()->route('proxmox.index');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error agregando el cluster.');
        }
    }

    /**
     * Muestra los datos de un cluster y sus nodos, qemus y storages asociados.
     *
     * @param string $name El nombre del cluster.
     * @return \Illuminate\View\View La vista que muestra los datos del cluster.
     */
    public function showbyIdCluster($name)
    {
        try {
            // Obtiene el cluster por su nombre
            $cluster = cluster::find($name);

            if (!$cluster) {
                throw new \Exception('Cluster no encontrado');
            }

            // Obtiene los nodos asociados al cluster
            $nodes = node::where('cluster_name', $name)->get();

            // Obtiene los IDs de los nodos
            $nodeIds = $nodes->pluck('id_proxmox')->toArray();

            // Obtiene los qemus asociados a los nodos
            $qemus = Qemu::whereIn('node_id', $nodeIds)->get();

            // Obtiene los storages asociados a los nodos
            $storages = storage::whereIn('node_id', $nodeIds)->where('storage', '!=', 'local')
                ->where('storage', '!=', 'local-lvm')
                ->where('storage', '!=', 'Backup')
                ->where('storage', '!=', 'Backup-Vicidial')->get();

            $uniqueNames = [];
            $filteredStorages = [];
            $totalUsedDisk = 0;
            $totalMaxDisk = 0;

            foreach ($storages as $storage) {
                if (!in_array($storage->storage, $uniqueNames)) {
                    $uniqueNames[] = $storage->storage;

                    if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial') {

                        $filteredStorages[] = $storage;
                        // Suma al total usado y al tamaño máximo a medida que filtras los storages
                        $totalUsedDisk += $storage->disk;
                        $totalMaxDisk += $storage->maxdisk;
                    }
                }
            }

            // Inicializa un arreglo para almacenar las sumas de size por node_id
            $sizeSumByNodeId = [];

            // Inicializa un arreglo para almacenar las sumas de maxdisk por node_id
            $storageLocalMax = [];

            // Calcula la suma de maxdisk del storage asociado a cada node_id
            // y almacena los resultados en el arreglo storageLocalMax
            // No se suman los storages con nombre "Backup-Virt"
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

            // Retorna la vista con los datos del cluster y sus asociaciones
            return view(
                'proxmox.cluster.show',
                compact('cluster'),
                [
                    'nodes' => $nodes,
                    'qemus' => $qemus,
                    'storages' => $filteredStorages,
                    'storageLocal' => $sizeSumByNodeId,
                    'storageLocalMax' => $storageLocalMax
                ]
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while retrieving cluster data.');
        }
    }

    /**
     * Exporta los datos de los Qemu en formato CSV.
     *
     * @return \Illuminate\Http\Response
     */
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
            $qemu->maxmem = ($qemu->maxmem / 1024 / 1024 / 1024) . " Gb";
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($qemus, ['id_proxmox', 'name', 'status', 'node_id', 'size', 'vmid', 'maxcpu', 'maxmem',  'type', 'cluster_name', 'storageName'])->download();
    }

    /**
     * Busca las instancias Qemu que coincidan con el término de búsqueda proporcionado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function searchQemu(Request $request)
    {
        $search = $request->get('search');
        $qemusID = Qemu::where('vmid', 'like', '%' . $search . '%')->paginate(1000)->appends(['search' => $search]);
        $qemus = Qemu::where('name', 'like', '%' . $search . '%')->paginate(1000)->appends(['search' => $search]);
        $qemus = $qemus->merge($qemusID);
        return view('proxmox.qemu', ['qemus' => $qemus]);
    }

    /**
     * Busca los nodos que coincidan con el término de búsqueda proporcionado y realiza
     * operaciones relacionadas con los nodos y las instancias Qemu asociadas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
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
    }

    /**
     * Busca almacenamiento basado en el término de búsqueda dado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function searchStorage(Request $request)
    {
        $search = $request->get('search');
        $storages = Storage::where('storage', 'like', '%' . $search . '%')->paginate(100)->appends(['search' => $search]);

        $uniqueNames = [];
        $totalUsedDisk = 0;
        $totalMaxDisk = 0;

        foreach ($storages as $storage) {
            if (!in_array($storage->storage, $uniqueNames)) {
                $uniqueNames[] = $storage->storage;
                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial') {

                    $filteredStorages[] = $storage;
                    $totalUsedDisk += $storage->disk;
                    $totalMaxDisk += $storage->maxdisk;
                }
            }
        }

        return view('proxmox.storage', ['storages' => $storages, 'filteredStorages' => $filteredStorages, 'totalUsedDisk' => $totalUsedDisk, 'totalMaxDisk' => $totalMaxDisk]);
    }

    /**
     * Muestra los nodos y sus detalles asociados según el ID del nodo.
     *
     * @param string $node El ID del nodo.
     * @return \Illuminate\View\View La vista con los nodos, qemus, storages y las sumas de tamaño por node_id.
     */
    public function showByIdNode($node)
    {
        try {
            $node = 'node/' . $node;

            $nodes = node::where('id_proxmox', $node)->get();
            foreach ($nodes as $node) {
                $qemus = Qemu::where('node_id', $node->id_proxmox)->get();
                $storages = Storage::where('node_id', $node->id_proxmox)->where('storage', '!=', 'local')
                    ->where('storage', '!=', 'local-lvm')
                    ->where('storage', '!=', 'Backup')
                    ->where('storage', '!=', 'Backup-Vicidial')->get();
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


            $uniqueNames = [];
            $filteredStorages = [];
            $totalUsedDisk = 0;
            $totalMaxDisk = 0;

            foreach ($storages as $storage) {
                if (!in_array($storage->storage, $uniqueNames)) {
                    $uniqueNames[] = $storage->storage;

                    if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial') {

                        $filteredStorages[] = $storage;
                        // Suma al total usado y al tamaño máximo a medida que filtras los storages
                        $totalUsedDisk += $storage->disk;
                        $totalMaxDisk += $storage->maxdisk;   
                    }
                }
            }

            return view(
                'proxmox.node.show',
                [
                    'nodes' => $nodes,
                    'qemus' => $qemus,
                    'storages' => $filteredStorages,
                    'storageLocal' => $sizeSumByNodeId,
                    'storageLocalMax' => $storageLocalMax
                ]
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->view('error', ['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina las instancias Qemu que tienen el estado "eliminado" y vacía la tabla QemuDeleted.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyQemu()
    {
        try {
            /* Qemu::where('status', 'eliminado')->delete(); */
            QemuDeleted::truncate();

            return redirect()->route('proxmox.index');
        } catch (\Exception $e) {
            return response()->view('error', ['message' => $e->getMessage()], 500);
        }
    }
}
