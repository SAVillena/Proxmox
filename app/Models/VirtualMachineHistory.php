<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualMachineHistory extends Model
{
    protected $fillable = [
        'date',
        'cluster_name',
        'cluster_qemus',
        'cluster_cpu',
        'cluster_memory',

    ];
}
