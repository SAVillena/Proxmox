<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualMachineHistory extends Model
{
    protected $fillable = [
        'date',
        'total_machines',
        'total_machines_running',
        'total_machines_stopped',
    ];
}
