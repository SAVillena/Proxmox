<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Models\qemu;
use App\Models\Storage;
use App\Models\node;

class ProxmoxService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getAuthToken()
    {
        try {
            $username = env('PROXMOX_USERNAME');
            $password = env('PROXMOX_PASSWORD');
            $URL = env('PROXMOX_URL_TICKET');
           
            $response = $this->client->request('POST', $URL, [
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
            $data = json_decode($body, true);
            return $data['data'];
        } catch (GuzzleException $e) {
            // Manejo de excepciones
            // Mostrar el error
            dd($e->getMessage());
            return null;
        }
    }

    public function getProxmoxData($data)
    {
        try {
            $URLGET = env('PROXMOX_URL_CLUSTER');
            $response = $this->client->request('GET', $URLGET.'/cluster/resources', [
                'headers' => [
                    'CSRFPreventionToken' => $data['CSRFPreventionToken'],
                    'Cookie' => "PVEAuthCookie={$data['ticket']}"
                ],
                'verify' => false
            ]);

            $body = $response->getBody();
            $data = json_decode($body, true);

            $this->saveDataToDatabase($data['data']);
            /* $this->updateStatusForStaleData(); */
        } catch (GuzzleException $e) {
            // Manejo de excepciones
            // Considera registrar el error o re-lanzar la excepción
            dd($e->getMessage());
        }
    }

   

    protected function saveDataToDatabase($data)
    {
        foreach ($data as $item) {
            
            
            //realizar la insersion si el type es qemu
            
            if ($item['type'] == 'qemu') {
                Qemu::updateOrCreate(
                    ['id_proxmox' => $item['id']],
                    [
                        'name' => $item['name'],
                        'type' => $item['type'],
                        'status' => $item['status'],
                        'cpu' => $item['cpu'],
                        'maxcpu' => $item['maxcpu'],
                        'diskwrite' => $item['diskwrite'],
                        'maxdisk' => $item['maxdisk'],
                        'netout' => $item['netout'],
                        'mem' => $item['mem'],
                        'maxmem' => $item['maxmem'],
                        'uptime' => $item['uptime'],
                        'disk' => $item['disk'],
                        'node' => $item['node'],
                        'netin' => $item['netin'],
                        
                        
                        ]
                    );
                }
            if ($item['type'] == 'node') {

                //aqui va una funcion de peticion a la api de proxmox para obtener los datos de la maquina virtual
                //funcion que hace peticion de datos a la api de proxmox
                // $this->getQemuData($data, $item['node']);
                
                node::updateOrCreate(
                    ['id_proxmox' => $item['id']],
                    [
                        'type' => $item['type'],
                        'status' => $item['status'],
                        'maxdisk' => $item['maxdisk'],
                        'disk' => $item['disk'],
                        'node' => $item['node'],
                        'uptime' => $item['uptime'],
                        /* 'cgroup_mode' => $item['cgroup_mode'], */
                        'mem' => $item['mem'],
                        'maxmem' => $item['maxmem'],
                        'cpu' => $item['cpu'],
                        'maxcpu' => $item['maxcpu'],
                        
                    ]
                );
            }
            if ($item['type'] == 'storage') {
                Storage::updateOrCreate(
                    ['id_proxmox' => $item['id']],
                    [
                        'type' => $item['type'],
                        'status' => $item['status'],
                        'maxdisk' => $item['maxdisk'],
                        'disk' => $item['disk'],
                        'node' => $item['node'],
                        'storage' => $item['storage'],
                        'content' => $item['content'],
                        'plugintype' => $item['plugintype'],
                        'shared' => $item['shared'],
                        'used' => ($item['disk'] / $item['maxdisk']),
                    ]
                );
            }
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

    public function CalculateStorage($storage)
    {
        if ($storage->name =! 'Backup-Virt') {
            $storageLocal = ($storage->disk);
            $storageLocalMax = ($storage->maxdisk);
            return [$storageLocal, $storageLocalMax];
        }



        
    }   
}
