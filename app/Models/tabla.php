<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tabla extends Model
{
    protected $fillable = [
        'id_proxmox',
        'type',
        'status',
        'maxdisk',
        'disk',
        'node',
        'uptime',
        'cgroup_mode',
        'mem',
        'maxmem',
        'maxcpu',
        'cpu',
        'level',
    ];
    protected $table = 'tabla';
    protected $primaryKey = 'id_proxmox';
}