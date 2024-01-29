<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class qemu extends Model
{
    protected $primaryKey = 'id_proxmox';
    public $incrementing = false;
    protected $fillable = [
        'id_proxmox',
        'vmid',
        'name',
        'type',
        'status',
        'disk',
        'maxdisk',
        'node_id',
        'uptime',
        'mem',
        'maxmem',
        'cpu',
        'maxcpu',
        'netin',
        'netout',
        'storageName',
        'size',
        'cluster_name',
    ];

      public function node()
    {
        return $this->belongsTo(Node::class, 'node_id', 'id_proxmox');
    }

    public function disks()
    {
        return $this->hasMany(Storage::class);
    } 


}
