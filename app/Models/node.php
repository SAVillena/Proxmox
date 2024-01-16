<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    protected $primaryKey = 'id_proxmox';
    public $incrementing = false;
    protected $fillable = [
        'cluster_name',
        'id_proxmox',
        'name',
        'ip',
        'type',
        'online',
        'status',
        'disk',
        'maxdisk',
        'node',
        'mem',
        'maxmem',
        'cpu',
        'maxcpu',
        'uptime',
    ];

    public function storages()
    {
        return $this->hasMany(Storage::class);
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class, 'cluster_name', 'name');
    }

    public function qemus()
    {
        return $this->hasMany(Qemu::class);
    }
}
