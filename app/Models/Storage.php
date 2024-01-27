<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Storage extends Model
{
    protected $primaryKey = 'id_proxmox';
    public $incrementing = false;
    protected $fillable = [
        'id_proxmox',
        'storage',
        'type',
        'status',
        'disk',
        'maxdisk',
        'node_id',
        'content',
        'plugintype',
        'shared',
        'used',
        'cluster',
    ];

    public function node()
    {
        return $this->belongsTo(Node::class, 'node_id', 'id_proxmox');
    }

}
