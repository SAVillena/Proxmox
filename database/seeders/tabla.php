<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tabla extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('tabla')->updateOrInsert([
            'id_proxmox' => '1',
            'type' => 'qemu',
            'status' => 'running',
            'maxdisk' => '10737418240',
            'disk' => '10737418240',
            'node' => 'pve',
            'uptime' => '0',
            'mem' => '1073741824',
            'maxmem' => '1073741824',
            'maxcpu' => '1',
            'cpu' => '0.6',
            'level' => '0',
        ]);
            
    }
}
