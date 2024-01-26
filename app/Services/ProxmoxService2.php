<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\qemu;
use App\Models\Storage;
use App\Models\node;
use App\Models\cluster;
use App\Models\ClusterCredentials;
use Illuminate\Support\Facades\Crypt;
use App\Models\VirtualMachineHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Debug\VirtualRequestStack;
use App\Models\QemuDeleted;

class ProxmoxService2
{
    protected $client;
    protected $baseUrl;
    protected $nodeInfo = [];
    protected $clusterName;
    protected $updatedQemuIds = [];

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl;
    }

    /**
     * Obtiene un token de autenticación de la API de Proxmox.
     *
     * @param string $ip La dirección IP del servidor Proxmox.
     * @param string $username El nombre de usuario para la autenticación.
     * @param string $password La contraseña encriptada para la autenticación.
     * @return array|null Los datos de autenticación o null si ocurrió un error.
     */
    public function getAuthToken($ip, $username, $password)
    {
        try {
            $URL = 'https://' . $ip . ':8006/api2/json';

            $password = Crypt::decrypt($password);

            $response = $this->client->request('POST', $URL . '/access/ticket', [
                'form_params' => [
                    'username' => $username,
                    'password' => $password,
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'verify' => false
            ]);

            $body = $response->getBody();
            $authData = json_decode($body, true);
            return $authData['data'];
        } catch (GuzzleException $e) {
            Log::error($e->getMessage() . "AUTH");
            return null;
        }
    }

    /**
     * Obtiene los datos de Proxmox.
     *
     * @param array $authData Los datos de autenticación.
     * @return void
     */
    public function getProxmoxData($authData)
    {
        $this->clusterName = null;
        $this->fetchAndSaveClusterStatus($authData);
        $this->fetchAndSaveClusterResources($authData);
    }

    /**
     * Obtiene y guarda el estado del clúster.
     *
     * @param array $authData Los datos de autenticación.
     * @return void
     */
    protected function fetchAndSaveClusterStatus($authData)
    {
        $this->clusterName = null;
        $this->nodeInfo = [];
        $url = $this->baseUrl . '/cluster/status';
        $data = $this->makeRequest('GET', $url, $authData);
        usort($data, function ($item1, $item2) {
            return strcmp($item1['type'], $item2['type']);
        });
        if ($data) {
            // Guarda todos los nombres de los nodos de $data en $this->nodeInfo
            foreach ($data as $item) {
                if ($item['type'] == 'node') {
                    $this->nodeInfo[$item['id']] = $item;
                }
            }
            foreach ($data as $item) {
                if ($item['type'] == 'node') {
                    $this->nodeInfo[$item['id']] = $item;
                } else if ($item['type'] == 'cluster') {
                    $nameNode = array_column($this->nodeInfo, 'name');
                    $ipNode = array_column($this->nodeInfo, 'ip');
                    $nameNode = implode(',', $nameNode);
                    $this->saveDataToCluster($item, $nameNode);
                    $this->clusterName = $item['name'];
                }
            }
        }
    }

    /**
     * Obtiene y guarda los recursos del clúster.
     *
     * @param array $authData Los datos de autenticación.
     * @return void
     */
    protected function fetchAndSaveClusterResources($authData)
    {
        $url = $this->baseUrl . '/cluster/resources';
        $data = $this->makeRequest('GET', $url, $authData);
        // Ordenar los datos por tipo
        usort($data, function ($item1, $item2) {
            return strcmp($item1['type'], $item2['type']);
        });
        if ($data) {
            // Procesar y guardar los datos
            foreach ($data as $item) {
                if ($item['type'] == 'node') {
                    $IP = $this->nodeInfo[$item['id']] ?? null;
                    $this->fetchAndSaveNodeData($item, $IP);
                }
                if ($item['type'] == 'qemu') {
                    $this->fetchAndSaveQemuData($authData, $item);
                }
                if ($item['type'] == 'storage') {
                    $this->fetchAndSaveStorageData($item);
                }
            }
        }
    }


    /**
     * Guarda los datos en el clúster.
     *
     * @param array $item Los datos del ítem a guardar.
     * @param array $nodeInfo La información de los nodos.
     * @return void
     */
    protected function saveDataToCluster($item, $nodeInfo)
    {
        // Guardar los datos en la base de datos
        cluster::updateOrCreate(
            ['id_proxmox' => $item['name']],
            [

                'type' => $item['type'],
                'name' => $item['name'],
                'node_count' => $item['nodes'],
                'nodes' => $nodeInfo
            ]
        );
    }

    /**
     * Obtiene y guarda los datos del nodo en la base de datos.
     *
     * @param array $nodeItem Los datos del nodo a guardar.
     * @param array $IP Los datos de IP asociados al nodo.
     * @return void
     */
    protected function fetchAndSaveNodeData($nodeItem, $IP)
    {
        if ($nodeItem) {
            // Guardar los datos en la base de datos
            node::updateOrCreate(
                ['id_proxmox' => $nodeItem['id']],
                [
                    'cluster_name' => $this->clusterName,
                    'type' => $nodeItem['type'],
                    'ip' => $IP['ip'],
                    'online' => $IP['online'],
                    'status' => $nodeItem['status'],
                    'disk' => $nodeItem['disk'],
                    'maxdisk' => $nodeItem['maxdisk'],
                    'node' => $nodeItem['node'],
                    'mem' => $nodeItem['mem'],
                    'maxmem' => $nodeItem['maxmem'],
                    'cpu' => $nodeItem['cpu'],
                    'maxcpu' => $nodeItem['maxcpu'],
                    'uptime' => $nodeItem['uptime'],
                ]
            );
        }
    }

    /**
     * Obtiene y guarda los datos de almacenamiento en la base de datos.
     *
     * @param array|null $storageItem Los datos del elemento de almacenamiento.
     * @return void
     */
    protected function fetchAndSaveStorageData($storageItem)
    {
        if ($storageItem) {
            //si en $storageItem no existe plugintype y content poner null
            if (!isset($storageItem['plugintype'])) {
                $storageItem['plugintype'] = null;
                $storageItem['content'] = null;
            }
            // Guardar los datos en la base de datos
            Storage::updateOrCreate(
                ['id_proxmox' => $storageItem['id']],
                [
                    'node_id' => 'node/' . $storageItem['node'],
                    'storage' => $storageItem['storage'],
                    'type' => $storageItem['type'],
                    'status' => $storageItem['status'],
                    'disk' => $storageItem['disk'],
                    'maxdisk' => $storageItem['maxdisk'],
                    'node' => $storageItem['node'],
                    'content' => $storageItem['content'],
                    'plugintype' => $storageItem['plugintype'],
                    'shared' => $storageItem['shared'],
                    'used' => ($storageItem['disk'] / $storageItem['maxdisk']),
                ]
            );
        }
    }


    /**
     * Recupera y guarda los datos de Qemu.
     *
     * @param array $authData Los datos de autenticación.
     * @param array $qemuItem Los datos del item Qemu.
     * @return void
     */
    protected function fetchAndSaveQemuData($authData, $qemuItem)
    {
        $nodeId = $qemuItem['node'];
        $vmid = $qemuItem['vmid'];
        if ($qemuItem) {
            $configUrl = $this->baseUrl . "/nodes/{$nodeId}/qemu/{$vmid}/config";
            $configData = $this->makeRequest('GET', $configUrl, $authData);
            $this->updatedQemuIds[] = $qemuItem['node'] . '/' . $qemuItem['id'];
            if ($configData) {

                $disk = $this->calculateTotalDiskSize($configData);
                // Guardar los datos combinados en la base de datos
                Qemu::updateOrCreate(
                    ['id_proxmox' => $qemuItem['node'] . '/' . $qemuItem['id']],
                    [
                        'node_id' => 'node/' . $qemuItem['node'],
                        'vmid' => $qemuItem['vmid'],
                        'name' => $qemuItem['name'],
                        'type' => $qemuItem['type'],
                        'status' => $qemuItem['status'],
                        'cpu' => $qemuItem['cpu'],
                        'maxcpu' => $qemuItem['maxcpu'],
                        'diskwrite' => $qemuItem['diskwrite'],
                        'maxdisk' => $qemuItem['maxdisk'],
                        'netout' => $qemuItem['netout'],
                        'mem' => $qemuItem['mem'],
                        'maxmem' => $qemuItem['maxmem'],
                        'uptime' => $qemuItem['uptime'],
                        'disk' => $qemuItem['disk'],
                        'netin' => $qemuItem['netin'],
                        'storageName' => $disk['storageNames'],
                        'size' => $disk['totalSize'],
                    ]
                );
            }
        }
    }

    /**
     * Calcula el tamaño total del disco y los nombres de los storages.
     *
     * @param array $configData Los datos de configuración.
     * @return array El tamaño total del disco y los nombres de los storages.
     */
    protected function calculateTotalDiskSize($configData)
    {
        $totalSize = 0;
        $storageNames = [];
        foreach ($configData as $key => $value) {
            if (strpos($value, '-disk-') !== false) {
                $diskInfo = $this->extractDiskInfo($value);
                if ($diskInfo && isset($diskInfo['size'])) {
                    $totalSize += $this->convertToGigabytes($diskInfo['size']);
                }
                // Extraer el nombre del storage
                $storageName = explode(':', $value)[0];
                if (!in_array($storageName, $storageNames)) {
                    $storageNames[] = $storageName;
                }
            }
        }

        // Convertir el array de nombres de almacenamiento a un string
        $storageNamesString = implode(', ', $storageNames);

        // Puedes retornar tanto el tamaño total como los nombres de los storages
        return ['totalSize' => $totalSize, 'storageNames' => $storageNamesString];
    }



    /**
     * Extrae información del disco a partir de una cadena de disco dada.
     *
     * @param string $diskString La cadena de disco de la cual extraer información.
     * @return array|null Un array que contiene la información del disco extraída, o null si no se encontró información.
     */
    protected function extractDiskInfo($diskString)
    {
        if (preg_match('/size=(\d+(?:\.\d+)?)G/', $diskString, $matches)) {
            return ['size' => $matches[1]];
        }
        return null;
    }

    /**
     * Convierte el tamaño de almacenamiento a gigabytes.
     *
     * @param string|int $size El tamaño de almacenamiento a convertir.
     * @return int El tamaño en gigabytes.
     */
    protected function convertToGigabytes($size)
    {
        //cambiar de T a Bytes
        if (strpos($size, 'T') !== false) {
            $size = str_replace('T', '', $size);
            $size = $size * 1024 * 1024 * 1024;
        }
        //cambiar de G a Bytes
        if (strpos($size, 'G') !== false) {
            $size = str_replace('G', '', $size);
            $size = $size * 1024 * 1024;
        }

        return $size; // Ajustar si la conversión es necesaria
    }



    /**
     * Realiza una solicitud a la URL especificada utilizando el método HTTP y los datos de autenticación proporcionados.
     *
     * @param string $method El método HTTP a utilizar para la solicitud (por ejemplo, GET, POST, PUT, DELETE).
     * @param string $url La URL a la que se enviará la solicitud.
     * @param array $authData Los datos de autenticación requeridos para la solicitud.
     * @return mixed Los datos de respuesta de la solicitud, o null si se produjo un error.
     */
    protected function makeRequest($method, $url, $authData)
    {
        try {
            $headers = [
                'CSRFPreventionToken' => $authData['CSRFPreventionToken'],
                'Cookie' => "PVEAuthCookie={$authData['ticket']}",
                'Content-Type' => 'application/json',
            ];
            $response = $this->client->request($method, $url, ['headers' => $headers, 'verify' => false, 'timeout' => 200]);
            $json = json_decode($response->getBody(), true);
            return $json['data'];
        } catch (GuzzleException $e) {
            Log::error($e->getMessage() . "request");
            dd($e->getMessage());
            return null;
        }
    }


    /**
     * Extrae el nombre y el tamaño de un elemento de almacenamiento.
     *
     * @param string $scsiString La cadena de configuración del elemento de almacenamiento.
     * @return array Un array con el nombre y el tamaño del elemento de almacenamiento.
     */
    function extractStorageInfo($scsiString)
    {
        // Extraer el nombre del storage
        $storageName = explode(':', $scsiString)[0];

        // Encontrar y extraer el tamaño en gigabytes o terabytes
        preg_match('/size=(\d+(?:G|T))/', $scsiString, $matches);
        $size = $matches[1] ?? null; // El tamaño estará en $matches[1] si la expresión regular encuentra una coincidencia


        return [
            'storageName' => $storageName,
            'size' => $size
        ];
    }


    /**
     * Procesa los nodos del clúster.
     *
     * Este método recorre todos los nodos del clúster y realiza las siguientes acciones:
     * - Obtiene los nombres de todos los clústeres existentes.
     * - Obtiene los nodos asociados a cada clúster.
     * - Incluye los nodos que no tienen un clúster asociado.
     * - Realiza una solicitud a cada nodo para obtener los datos de Proxmox.
     * - Marca los QEMU que ya no están presentes como eliminados.
     * 
     * @return void
     */
    public function processClusterNodes()
    {
        $NameAllCluster = cluster::all()->pluck('name')->toArray();
        $nodes = Node::whereIn('cluster_name', $NameAllCluster)->get();
        //incluir los nodos con cluster_name = null
        $nodesNull = Node::where('cluster_name', null)->get();
        $nodes = $nodes->merge($nodesNull);


        foreach ($nodes as $node) {
            $this->baseUrl = 'https://' . $node->ip . ':8006/api2/json';

            try {

                $credentials = ClusterCredentials::where('ip', $node->ip)->first();
                $authData = $this->getAuthToken($node->ip, $credentials->username, $credentials->password);

                if ($authData) {
                    $this->getProxmoxData($authData);
                }
            } catch (GuzzleException $e) {
                // Si la autenticación falla, se podría registrar el error o intentar con el siguiente nodo
                Log::error("Error al conectar con el nodo: " . $node->ip);
                continue;
            }
        }
    }

    public function resetUpdatedQemuIds()
    {
        $this->updatedQemuIds = [];
    }

    /**
     * Agrega un clúster al servidor Proxmox.
     *
     * @param string $ip La dirección IP del servidor Proxmox.
     * @param string $username El nombre de usuario para la autenticación.
     * @param string $password La contraseña para la autenticación.
     * @return void
     */
    public function addCluster($ip, $username, $password)
    {
        $this->baseUrl = 'https://' . $ip . ':8006/api2/json';
        $passwordEncrypt = Crypt::encrypt($password);
        try {
            $authData = $this->getAuthToken($ip, $username, $passwordEncrypt);
            if ($authData) {
                $this->getProxmoxData($authData);
                $this->addClusterCredentials($ip, $username, $password);
                $this->VMHistory();
            } else {
                Log::error("Error al conectar con el nodo: " . $ip);
            }
        } catch (GuzzleException $e) {
            // Si la autenticación falla, se podría registrar el error o intentar con el siguiente nodo

            Log::error("Error al conectar con el nodo: " . $ip);
        }
    }


    /**
     * Agrega las credenciales del clúster.
     *
     * @param string $ip La dirección IP del clúster.
     * @param string $username El nombre de usuario para autenticarse en el clúster.
     * @param string $password La contraseña para autenticarse en el clúster.
     * @return void
     */
    public function addClusterCredentials($ip, $username, $password)
    {
        //recorre el array de ips y si encuentra la ip en la base de datos
        //actualiza los datos, sino crea un nuevo registro
        $this->baseUrl = 'https://' . $ip . ':8006/api2/json';
        $url = $this->baseUrl . '/cluster/status';
        $passwordEncrypt = Crypt::encrypt($password);
        $authData = $this->getAuthToken($ip, $username, $passwordEncrypt);
        $data = $this->makeRequest('GET', $url, $authData);
        usort($data, function ($item1, $item2) {
            return strcmp($item1['type'], $item2['type']);
        });

        //si es un nodo guardar la ip en un array
        if ($data) {
            foreach ($data as $item) {
                if ($item['type'] == 'node') {
                    $IpNodes[] = $item['ip'];
                }
            }
        }
        foreach ($IpNodes as $ipNode) {
            $credentials = ClusterCredentials::where('ip', $ipNode)->first();
            if ($credentials) {
                $credentials->username = $username;
                $credentials->password = Crypt::encrypt($password);
                $credentials->save();
            } else {
                ClusterCredentials::create([
                    'ip' => $ipNode,
                    'username' => $username,
                    'password' => Crypt::encrypt($password),
                ]);
            }
        }
    }

    /**
     * Realiza el historial de las máquinas virtuales para cada clúster y lo guarda en la tabla VirtualMachineHistory.
     *
     * @return void
     */
    public function VMHistory()
    {
        try {
            // Nombre de los clústeres
            $NameAllCluster = cluster::all()->pluck('name')->toArray();

            // Fecha de inicio y fin del mes actual
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            // Recorrer los nodos del clúster
            foreach ($NameAllCluster as $nameCluster) {
                // Inicializar totales
                $TotalCPU = 0;
                $TotalRAM = 0;
                $TotalQemus = 0;
                $TotalDisk = 0;

                // Filtrar las QEMUs creadas en el mes actual y que pertenecen al clúster
                $qemus = Qemu::whereHas('node', function ($query) use ($nameCluster) {
                    $query->where('cluster_name', $nameCluster);
                })
                    ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->get();

                // Calcular totales
                $TotalQemus = $qemus->count();
                $TotalCPU = $qemus->sum('maxcpu');
                $TotalRAM = $qemus->sum('maxmem');
                $TotalDisk = $qemus->sum('maxdisk');

                // Guardar o actualizar información en la tabla de historial
                VirtualMachineHistory::updateOrCreate(
                    [
                        'date' => $startOfMonth,
                        'cluster_name' => $nameCluster
                    ],
                    [
                        'cluster_qemus' => $TotalQemus,
                        'cluster_cpu' => $TotalCPU,
                        'cluster_memory' => $TotalRAM,
                        'cluster_disk' => $TotalDisk,
                    ]
                );
            }
        } catch (GuzzleException $e) {
            Log::error("Error occurred: " . $e->getMessage());
        }
    }

    /**
     * Marca los QEMU no recibidos en las peticiones como eliminados.
     *
     * @return void
     */
    public function markMissingQemuAsDeleted()
    {
        $allQemuIds = Qemu::pluck('id_proxmox')->all();
        $qemusToMarkDeleted = array_diff($allQemuIds, $this->updatedQemuIds);

        // Marcar como 'eliminado' los QEMU que ya no están presentes
        $QemuDeleted = Qemu::whereIn('id_proxmox', $qemusToMarkDeleted)->get();

        //sacar solo el id_proxmox de $QemuDeleted
        $QemuDelete = $QemuDeleted->pluck('id_proxmox');

        // Qemu::whereIn('id_proxmox', $qemusToMarkDeleted)->delete();
        Qemu::whereIn('id_proxmox', $QemuDelete)->update(['status' => 'eliminado']);



        foreach ($QemuDeleted as $qemu) {
            QemuDeleted::updateOrCreate(
                ['id_proxmox' => $qemu->id_proxmox],
                [
                    'vmid' => $qemu->vmid,
                    'node_id' => $qemu->node_id,
                    'name' => $qemu->name,
                    'type' => $qemu->type,
                    'status' => 'eliminado',
                    'cpu' => $qemu->cpu,
                    'maxcpu' => $qemu->maxcpu,
                    'diskwrite' => $qemu->diskwrite,
                    'maxdisk' => $qemu->maxdisk,
                    'netout' => $qemu->netout,
                    'mem' => $qemu->mem,
                    'maxmem' => $qemu->maxmem,
                    'uptime' => $qemu->uptime,
                    'disk' => $qemu->disk,
                    'netin' => $qemu->netin,
                    'storageName' => $qemu->storageName,
                    'size' => $qemu->size,
                ]
            );

            //eliminar el estado de status de los qemus eliminados
            Qemu::where('status', 'eliminado')->delete();
        }
    }
}
