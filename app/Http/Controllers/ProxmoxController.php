<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\qemu;
use App\Models\Storage;
use App\Models\node;
use App\Models\cluster;
use App\Models\MonthlyTotal;
use App\Models\QemuDeleted;
use App\Models\VirtualMachineHistory;
use App\Models\ClusterCredentials;
use App\Services\ProxmoxService2;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Node_storage;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class ProxmoxController extends Controller
{
    //
    protected $proxmoxService;

    public function __construct(ProxmoxService2 $proxmoxService)
    {
        $this->middleware('auth', ['except' => ['getDataRedirect2']]);
        $this->middleware('can:manage cluster', ['only' => ['destroyNode', 'destroyCluster', 'storeCluster']]);
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
        $totalQemus = Qemu::count();

        $totalStorages = Storage::count();

        $totalCPU = Node::sum('maxcpu');
        $totalRAM = Node::sum('maxmem');

        $totalRAMQemu = Qemu::sum('maxmem');
    

        //cpuUsagePercentage, Node->cpu es el porcentaje de uso de cpu de cada nodo
        $cpuUsagePercentage = Node::sum('cpu');
        if ($cpuUsagePercentage == 0) {
            $cpuUsagePercentage = 0;
        } else {
            $cpuUsagePercentage = $cpuUsagePercentage / $totalCPU * 100;
        }

        //memoryUsagePercentage
        $totalRAMQemu = Qemu::sum('maxmem');
        if ($totalRAMQemu == 0) {
            $memoryUsagePercentage = 0;
        } else {
            $memoryUsagePercentage = $totalRAMQemu / $totalRAM * 100;
        }


        $storages = Storage::all();
        $uniqueNames = [];
        $filteredStorages = [];
        $totalUsedDisk = 0;
        $totalMaxDisk = 0;

        foreach ($storages as $storage) {
            if (!in_array($storage->storage, $uniqueNames)) {
                $uniqueNames[] = $storage->storage;

                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial' || $storage->cluster == null) {

                    $filteredStorages[] = $storage;
                    // Suma al total usado y al tamaño máximo a medida que filtras los storages
                    if($storage->storage != 'Backup-Virt'){
                    $totalUsedDisk += $storage->disk;
                    $totalMaxDisk += $storage->maxdisk;
                    }
                }
            }
        }
        if ($totalMaxDisk == 0) {
            $diskUsagePercentage = 0;
        } else {
            $diskUsagePercentage = $totalUsedDisk / $totalMaxDisk * 100;
        }

        //nodos con cluster name null
        $OnlyNodes = Node::where('cluster_name', null)->count();

        return view('proxmox.home', [
            'totalClusters' => $totalClusters,
            'totalNodes' => $totalNodes,
            'totalQemus' => $totalQemus,
            'totalStorages' => $totalStorages,
            'totalCPU' => $totals->totalCPU,
            'totalNodeCpu' => $totalCPU,
            'totalNodeRAM' => $totalRAM,
            'totalRAM' => $totalRAM,
            'totalDisk' => $totalMaxDisk,
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
            $this->proxmoxService->MonthlyTotals();
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
        $storages = storage::all();

        $uniqueNames = [];
        $filteredStorages = [];
        $totalUsedDisk = 0;
        $totalMaxDisk = 0;

        foreach ($storages as $storage) {
            if (!in_array($storage->storage, $uniqueNames)) {
                $uniqueNames[] = $storage->storage;

                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial' || $storage->cluster == null) {

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
        $nodesIdProxmox = $nodes->pluck('id_proxmox')->toArray();

        // Obtiene los qemus asociados a los nodos
        $qemus = Qemu::whereIn('node_id', $nodesIdProxmox)->get();

        // Obtiene los storages asociados a los nodos
        $storages = Storage::whereIn('node_id', $nodesIdProxmox)->get();

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
        $storages = storage::all();

        $uniqueNames = [];
        $filteredStorages = [];
        $totalUsedDisk = 0;
        $totalMaxDisk = 0;

        foreach ($storages as $storage) {
            if (!in_array($storage->storage, $uniqueNames)) {
                $uniqueNames[] = $storage->storage;

                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial' || $storage->cluster == null) {

                    $filteredStorages[] = $storage;
                    // Suma al total usado y al tamaño máximo a medida que filtras los storages
                    if($storage->storage != 'Backup-Virt'){
                    $totalUsedDisk += $storage->disk;
                    $totalMaxDisk += $storage->maxdisk;
                    }
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
            $nodesIdProxmox = $nodes->pluck('id_proxmox')->toArray();
            $qemus = Qemu::whereIn('node_id', $nodesIdProxmox)->get();
            $storages = Storage::where('cluster', $name)->get();



            //si existe otro nodo conectado mediante node_storage al storage no eliminar



            foreach ($qemus as $qemu) {
                $qemu->delete();
            }
            foreach ($nodes as $node) {
                Node_storage::where('node_id', $node->id)->delete();
                $node->delete();
            }
            foreach ($storages as $storage) {

                Node_storage::where('storage_id', $storage->id)->delete();
                $storage->delete();
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
    public function destroyNode($nodeId)
    {
        try {
            // Verificar si el usuario está autenticado
            if (!auth()->check()) {
                if (!request()->expectsJson()) {
                    return redirect()->route('login')->with('error', 'Debe iniciar sesión para realizar esta acción.');
                }
                return response()->json(['success' => false, 'message' => 'Debe iniciar sesión para realizar esta acción.'], 401);
            }

            // Verificar permisos (el middleware también debería capturar esto, pero por seguridad)
            if (!auth()->user()->can('manage cluster')) {
                if (!request()->expectsJson()) {
                    return redirect()->back()->with('error', 'No tiene permisos para eliminar nodos.');
                }
                return response()->json(['success' => false, 'message' => 'No tiene permisos para eliminar nodos.'], 403);
            }

            // Buscar el nodo por ID o por nombre
            if (is_numeric($nodeId)) {
                // Si es numérico, buscar por ID
                $node = Node::find($nodeId);
            } else {
                // Si no es numérico, buscar por nombre
                $node = Node::where('node', $nodeId)->first();
            }
            
            if (!$node) {
                // Para formularios tradicionales, redirigir con error
                if (!request()->expectsJson()) {
                    return redirect()->back()->with('error', 'Nodo no encontrado.');
                }
                // Para AJAX, responder JSON
                return response()->json(['success' => false, 'message' => 'Nodo no encontrado.'], 404);
            }

            Log::info("Eliminando nodo: {$node->node} (ID: {$node->id})");

            // Verificar solo dependencias críticas (VMs)
            $qemuCount = Qemu::where('node_id', $node->id_proxmox)->count();
            
            if ($qemuCount > 0) {
                Log::warning("Intento de eliminar nodo con VMs activas: QEMUs={$qemuCount}");
                $errorMessage = "No se puede eliminar el nodo. Tiene {$qemuCount} máquinas virtuales activas. Elimine primero las VMs.";
                
                if (!request()->expectsJson()) {
                    return redirect()->back()->with('error', $errorMessage);
                }
                return response()->json(['success' => false, 'message' => $errorMessage], 400);
            }

            // Obtener storages directamente asociados al nodo
            $directStorages = Storage::where('node_id', $node->id_proxmox)->get();
            
            // Obtener IDs de storages asociados a través de la tabla pivot
            $pivotStorageIds = Node_storage::where('node_id', $node->id)->pluck('storage_id');
            $pivotStorages = Storage::whereIn('id', $pivotStorageIds)->get();
            
            $allStorages = $directStorages->merge($pivotStorages)->unique('id');
            $storageCount = $allStorages->count();
            
            Log::info("Procesando eliminación: QEMUs={$qemuCount}, Storages directos={$directStorages->count()}, Storages pivot={$pivotStorages->count()}");

            // PRIMERO: Eliminar relaciones de la tabla pivot node_storage
            $deletedPivotRecords = Node_storage::where('node_id', $node->id)->delete();
            if ($deletedPivotRecords > 0) {
                Log::info("Eliminadas {$deletedPivotRecords} relaciones node_storage");
            }

            // SEGUNDO: Manejar storages directos (ahora sin referencias en node_storage)
            foreach ($directStorages as $storage) {
                // Para storages con relación directa, eliminar el registro una vez que no haya referencias
                Log::info("Eliminando storage directo '{$storage->storage}' del nodo");
                try {
                    $storage->delete();
                    Log::info("Storage '{$storage->storage}' eliminado exitosamente");
                } catch (\Exception $e) {
                    Log::warning("No se pudo eliminar storage '{$storage->storage}': " . $e->getMessage());
                    // Continuar con la eliminación del nodo incluso si algunos storages no se pueden eliminar
                }
            }

            // Eliminar credenciales asociadas
            ClusterCredentials::where('ip', $node->ip)->delete();
            Log::info("Credenciales eliminadas para IP: {$node->ip}");
            
            // Eliminar el nodo
            $node->delete();
            
            Log::info("Nodo eliminado exitosamente: {$nodeId}");
            
            $successMessage = "Nodo eliminado exitosamente. Se procesaron {$storageCount} almacenamientos asociados.";
            
            if (!request()->expectsJson()) {
                return redirect()->back()->with('success', $successMessage);
            }
            return response()->json(['success' => true, 'message' => $successMessage]);
            
        } catch (\Exception $e) {
            Log::error("Error al eliminar nodo {$nodeId}: " . $e->getMessage());
            
            if (!request()->expectsJson()) {
                return redirect()->back()->with('error', 'Error interno del servidor.');
            }
            return response()->json(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
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
            // Validar los datos de entrada
            $request->validate([
                'ip' => 'required|ip',
                'username' => 'required|string|min:2|max:50',
                'password' => 'required|string|min:1'
            ], [
                'ip.required' => 'La dirección IP es obligatoria.',
                'ip.ip' => 'Debe ser una dirección IP válida.',
                'username.required' => 'El nombre de usuario es obligatorio.',
                'username.min' => 'El nombre de usuario debe tener al menos 2 caracteres.',
                'username.max' => 'El nombre de usuario no puede tener más de 50 caracteres.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 1 caracter.'
            ]);
            
            $ip = $request->input('ip');
            $username = $request->input('username');
            $password = $request->input('password');
            
            // Intentar agregar el cluster/nodo
            $this->proxmoxService->addCluster($ip, $username, $password);
            
            Log::info("Cluster/Nodo agregado exitosamente: IP={$ip}, Usuario={$username}");
            
            return redirect()->route('proxmox.home')->with('success', 'Cluster/Nodo agregado exitosamente. Los datos han sido sincronizados.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-lanzar la excepción de validación para que Laravel la maneje
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al agregar cluster/nodo: ' . $e->getMessage());
            
            // Determinar el tipo de error para mostrar un mensaje más específico
            if (strpos($e->getMessage(), 'autenticar') !== false) {
                $errorMessage = 'Error de autenticación. Verifique el usuario y contraseña.';
            } elseif (strpos($e->getMessage(), 'conectar') !== false) {
                $errorMessage = 'Error de conexión. Verifique que la IP sea correcta y que el puerto 8006 esté accesible.';
            } else {
                $errorMessage = 'Error al procesar la solicitud: ' . $e->getMessage();
            }
            
            return redirect()->back()
                           ->withInput()
                           ->with('error', $errorMessage);
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
            $nodesIdProxmox = $nodes->pluck('id_proxmox')->toArray();

            // Obtiene los qemus asociados a los nodos
            $qemus = Qemu::whereIn('node_id', $nodesIdProxmox)->get();

            // Obtiene los storages asociados a los nodos
            $storages = storage::whereIn('node_id', $nodesIdProxmox)->get();

            $uniqueNames = [];
            $filteredStorages = [];
            $totalUsedDisk = 0;
            $totalMaxDisk = 0;

            foreach ($storages as $storage) {
                if (!in_array($storage->storage, $uniqueNames)) {
                    $uniqueNames[] = $storage->storage;

                    if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial' || $storage->cluster == null) {

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

            $totalDisk = 0;
            $totalRAM = 0;
            $totalCPU = 0;
            $totalRAMQemu = 0;
            $totalCPUQemu = 0;
            $totalDiskQemu = 0;

            foreach ($nodes as $node) {
                $totalRAM += $node->maxmem;
                $totalCPU += $node->maxcpu;
            }

            foreach ($qemus as $qemu) {
                $totalRAMQemu += $qemu->maxmem;
                $totalCPUQemu += $qemu->maxcpu;
                $totalDiskQemu += $qemu->size;
            }

            $totalQemu = $qemus->count();


            // Retorna la vista con los datos del cluster y sus asociaciones
            return view(
                'proxmox.cluster.show',
                compact('cluster'),
                [
                    'nodes' => $nodes,
                    'qemus' => $qemus,
                    'storages' => $filteredStorages,
                    'storageLocal' => $sizeSumByNodeId,
                    'storageLocalMax' => $storageLocalMax,
                    'totalRAM' => $totalRAM,
                    'totalCPU' => $totalCPU,
                    'totalRAMQemu' => $totalRAMQemu,
                    'totalCPUQemu' => $totalCPUQemu,
                    'totalMaxDisk' => $totalMaxDisk,
                    'totalDiskQemu' => $totalDiskQemu,
                    'totalQemu' => $totalQemu

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
        /* foreach ($qemus as $qemu) {
            $node = node::where('id_proxmox', $qemu->node_id)->first();
            $qemu->cluster_name = $node->cluster_name;
        } */

        //transformar el maxmem de bytes a gigabytes
        foreach ($qemus as $qemu) {
            $qemu->maxmem = ($qemu->maxmem / 1024 / 1024 / 1024) . " Gb";
            $qemu->size = ($qemu->size / 1024 / 1024 / 1024) . " Gb";
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($qemus, ['id_proxmox', 'name', 'status', 'node_id', 'size', 'vmid', 'maxcpu', 'maxmem',  'type', 'cluster_name', 'storageName'])->download();
    }

    /**
     * Exporta los datos de los nodos en formato CSV.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportNodeCSV()
    {
        $nodes = node::all();
        foreach ($nodes as $node) {
            $node->maxmem = round($node->maxmem / 1024 / 1024 / 1024,2) . " Gb";
            $node->maxdisk = round($node->maxdisk / 1024 / 1024 / 1024,2) . " Gb";
        }
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($nodes, ['id_proxmox', 'node', 'cluster_name','cpu', 'maxcpu', 'maxmem', 'disk', 'maxdisk', 'ip'])->download();
    }

    /**
     * Exporta los datos de los storages en formato CSV.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportStorageCSV()
    {
        $storages = storage::all();
        foreach ($storages as $storage) {
            $storage->disk = round($storage->disk / 1024 / 1024 / 1024 / 1024,2) . " Tb";
            $storage->maxdisk = round($storage->maxdisk / 1024 / 1024 / 1024 /2024,2) . " Tb";
        }
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($storages, ['id_proxmox', 'storage', 'node_id', 'disk', 'maxdisk', 'cluster'])->download();
    }

    /**
     * Exporta los datos del qemu del cluster en formato CSV.
     * 
     * @param string $name El nombre del cluster.
     * @return \Illuminate\Http\Response
     */
    public function exportQemuByClusterCSV($name)
    {
        $qemus = Qemu::where('cluster_name', $name)->get();
        foreach ($qemus as $qemu) {
            $qemu->maxmem = round($qemu->maxmem / 1024 / 1024 / 1024,2) . " Gb";
            $qemu->size = round($qemu->size / 1024 / 1024 / 1024,2) . " Gb";
        }

        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($qemus, ['cluster_name','id_proxmox', 'name', 'status', 'size', 'vmid', 'maxcpu', 'maxmem',  'storageName'])->download();
    }


    /**
     * Exporta los datos de los cluster en formato CSV
     * 
     * @return \Illuminate\Http\Response
     */
    public function exportClusterCSV()
    {
        $clusters = cluster::all();
        $csvExporter = new \Laracsv\Export();
        $csvExporter->build($clusters, ['name', 'node_count', 'nodes'])->download();
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
        $qemuByCluster = Qemu::where('cluster_name', 'like', '%' . $search . '%')->paginate(1000)->appends(['search' => $search]);
        $qemuByNode = Qemu::where('node_id', 'like', '%' . $search . '%')->paginate(1000)->appends(['search' => $search]);
        $qemus = $qemusID->merge($qemus)->merge($qemuByCluster)->merge($qemuByNode);
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
        $nodesIdProxmox = $nodes->pluck('id_proxmox')->toArray();
        $qemus = Qemu::whereIn('node_id', $nodesIdProxmox)->get();
        $storages = Storage::whereIn('node_id', $nodesIdProxmox)->get();

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
                if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial' || $storage->cluster == null) {

                    $filteredStorages[] = $storage;
                    if($storage->storage != 'Backup-Virt'){
                    $totalUsedDisk += $storage->disk;
                    $totalMaxDisk += $storage->maxdisk;
                    }
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


            $uniqueNames = [];
            $filteredStorages = [];
            $totalUsedDisk = 0;
            $totalMaxDisk = 0;

            foreach ($storages as $storage) {
                if (!in_array($storage->storage, $uniqueNames)) {
                    $uniqueNames[] = $storage->storage;

                    if ($storage->storage != 'local' && $storage->storage != 'local-lvm' && $storage->storage != 'Backup' && $storage->storage != 'Backup-Vicidial' || $storage->cluster == null) {

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
