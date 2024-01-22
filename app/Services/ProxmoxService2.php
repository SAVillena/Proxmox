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

    public function getAuthToken($ip, $username, $password)
    {
        try {
            /* $username = env('PROXMOX_USERNAME');
            $password = env('PROXMOX_PASSWORD') */;

            //realizar un for para que se conecte a todos los proxmox
            //con distintas urls
            $URL = 'https://' . $ip . ':8006/api2/json';

            $password = Crypt::decrypt($password);
            
            // $URL = env('PROXMOX_URL');

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
            // Manejo de excepciones
            // Mostrar el error
            error_log($e->getMessage());
            return null;
        
        }
    }

    public function getProxmoxData($authData)
    {
        $this->clusterName = null;
        $this->fetchAndSaveClusterStatus($authData);
        $this->fetchAndSaveClusterResources($authData);
    }

    protected function fetchAndSaveClusterStatus($authData)
    {
        $this->nodeInfo = [];
        $url = $this->baseUrl . '/cluster/status';
        $data = $this->makeRequest('GET', $url, $authData);
        usort($data, function ($item1, $item2) {
            return strcmp($item1['type'], $item2['type']);
        });
        if ($data) {
            //guarda todos los nombre de los nodos de $data en $this->nodeInfo
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
                    //agregar a clusterName el nombre del cluster
                    $this->clusterName = $item['name'];
                }
            }

            // Actualizar el estado de los registros que no se actualizaron
            // $this->updateStatusForStaleData();

        }
    }

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


    protected function saveDataToCluster($item, $nodeInfo)
    {
        // Guardar los datos en la base de datos
        cluster::updateOrCreate(
            ['id_proxmox' => $item['id']],
            [
                'type' => $item['type'],
                'name' => $item['name'],
                'node_count' => $item['nodes'],
                'nodes' => $nodeInfo
            ]
        );
    }

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

    protected function fetchAndSaveStorageData($storageItem)
    {
        if ($storageItem) {
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


    protected function fetchAndSaveQemuData($authData, $qemuItem)
    {
        $nodeId = $qemuItem['node'];
        $vmid = $qemuItem['vmid'];
        if ($qemuItem) {
            // Complementar la información de Qemu con la configuración específica
            $configUrl = $this->baseUrl . "/nodes/{$nodeId}/qemu/{$vmid}/config";
            $configData = $this->makeRequest('GET', $configUrl, $authData);
            if ($configData) {
                $this->updatedQemuIds[] = $qemuItem['node'].'/'.$qemuItem['id'];
                                
                $InfoStorage = $this->extractStorageInfo($configData['scsi0']);
                // Guardar los datos combinados en la base de datos
                Qemu::updateOrCreate(
                    ['id_proxmox' => $qemuItem['node'].'/'.$qemuItem['id']],
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
                        'storageName' => $InfoStorage['storageName'],
                        'size' => $InfoStorage['size'],
                    ]
                );
            }
        }
    }

    protected function makeRequest($method, $url, $authData)
    {
        try {
            $headers = [
                'CSRFPreventionToken' => $authData['CSRFPreventionToken'],
                'Cookie' => "PVEAuthCookie={$authData['ticket']}",
                'Content-Type' => 'application/json',
            ];
            $response = $this->client->request($method, $url, ['headers' => $headers, 'verify' => false]);
            $json = json_decode($response->getBody(), true);
            return $json['data'];
        } catch (GuzzleException $e) {
            dd($e->getMessage());
            return null;
        }
    }


    public function updateStatusForStaleData()
    {
        $oneHourAgo = now()->subHour();

        // Actualizar los registros donde la columna 'updated_at' es menor que $oneHourAgo
        // y donde 'type' es igual a 'qemu'
        Qemu::where('type', 'qemu')
            ->where('updated_at', '<', $oneHourAgo)->where('status', '!=', 'stopped')
            ->update(['status' => 'eliminado']);
    }

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

    public function processClusterNodes()
    {
        $this->updatedQemuIds = [];
        $NameAllCluster = cluster::all()->pluck('name')->toArray();
        $nodes = Node::where('cluster_name', $NameAllCluster)->get();
        //incluir los nodos con cluster_name = null
        $nodesNull = Node::where('cluster_name', null)->get();
        $nodes = $nodes->merge($nodesNull);


        foreach ($nodes as $node) {
            $this->baseUrl = 'https://' . $node->ip . ':8006/api2/json';

            try {
                
                $credentials = ClusterCredentials::where('ip', $node->ip)->first();
                $authData = $this->getAuthToken($node->ip,$credentials->username, $credentials->password);

                if ($authData) {
                    $this->getProxmoxData($authData);
                }
            } catch (GuzzleException $e) {
                // Si la autenticación falla, se podría registrar el error o intentar con el siguiente nodo
                Log::error("Error al conectar con el nodo: " . $node->ip);
                continue;
            }
        }
        $this->markMissingQemuAsDeleted();
    }

    public function addCluster($ip, $username, $password)
    {
        $this->baseUrl = 'https://' . $ip . ':8006/api2/json';
        $passwordEncrypt = Crypt::encrypt($password);
        try {
            $authData = $this->getAuthToken($ip, $username, $passwordEncrypt);
            if ($authData) {
                $this->getProxmoxData($authData);
                $this->addClusterCredentials($ip, $username, $password);

            }
            $this->VMHistory();
        } catch (GuzzleException $e) {
            // Si la autenticación falla, se podría registrar el error o intentar con el siguiente nodo
        }
    }
    

    public function addClusterCredentials($ip, $username, $password)
    {
        //recorre el array de ips y si encuentra la ip en la base de datos
        //actualiza los datos, sino crea un nuevo registro
        $this->baseUrl = 'https://' . $ip . ':8006/api2/json';
        $url = $this->baseUrl . '/cluster/status';
        $passwordEncrypt = Crypt::encrypt($password);
        $authData= $this->getAuthToken($ip, $username, $passwordEncrypt);
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

public function VMHistory()
{
    // Nombre de los clusters
    $NameAllCluster = cluster::all()->pluck('name')->toArray();

    // Fecha de inicio y fin del mes actual
    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth = Carbon::now()->endOfMonth();

    // Recorrer los nodos del cluster
    foreach ($NameAllCluster as $nameCluster) {
        // Inicializar totales
        $TotalCPU = 0;
        $TotalRAM = 0;
        $TotalQemus = 0;
        $TotalDisk = 0;

        // Filtrar QEMUs creadas en el mes actual y que pertenecen al cluster
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
}
    
    public function markMissingQemuAsDeleted()
    {
        $allQemuIds = Qemu::pluck('id_proxmox')->all();
        $qemusToMarkDeleted = array_diff($allQemuIds, $this->updatedQemuIds);

    // Marcar como 'eliminado' los QEMU que ya no están presentes
        $QemuDeleted = Qemu::whereIn('id_proxmox', $qemusToMarkDeleted)->get();
        
        // Qemu::whereIn('id_proxmox', $qemusToMarkDeleted)->delete();
        Qemu::whereIn('id_proxmox', $qemusToMarkDeleted)->update(['status' => 'eliminado']);
        
                

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
