<?php

namespace App\Http\Controllers;

use App\Models\VirtualMachineHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VirtualMachineHistoryController extends Controller
{
    //
    /**
     * Muestra el historial mensual de las máquinas virtuales.
     *
     * Este método recupera todos los registros del historial de las máquinas virtuales y calcula el total de qemu, cpu, memoria y disco para cada clúster.
     * También calcula la suma de qemu, cpu, memoria y disco para todos los clústeres de cada mes.
     *
     * @return \Illuminate\View\View
     */
    public function indexMonthly()
    {
        $VMHistory = VirtualMachineHistory::all();

        //suma todas las qemu de cada clúster, todas las cpu de cada clúster, todas las memoria de cada clúster, todas las disco de cada clúster por meses
        $total_qemus = VirtualMachineHistory::sum('cluster_qemus');
        $total_cpus = VirtualMachineHistory::sum('cluster_cpu');
        $total_memorys = VirtualMachineHistory::sum('cluster_memory');
    
        $total_disks = VirtualMachineHistory::sum('cluster_disk');

        //histories suma de todos los clusters del mes
        $histories = VirtualMachineHistory::selectRaw('date, SUM(cluster_qemus) as cluster_qemus, SUM(cluster_cpu) as cluster_cpu, SUM(cluster_memory) as cluster_memory, SUM(cluster_disk) as cluster_disk')
            ->groupBy('date')->get();

        return view('proxmox.virtualMachineHistory', compact('histories'), compact('total_qemus', 'total_cpus', 'total_memorys', 'total_disks', 'VMHistory'));
    }

    /**
     * Muestra el historial anual de las máquinas virtuales.
     *
     * Este método obtiene los historiales de las máquinas virtuales para todo el año.
     * Calcula el número total de QEMUs, CPUs, memoria y discos en todos los clústeres.
     * También calcula la suma de estos valores para cada fecha.
     *
     * @return \Illuminate\View\View
     */
    public function indexAnual()
    {
        // Obtiene los historiales de las máquinas virtuales para todo el año
        $VMHistory = VirtualMachineHistory::all();

        // Calcula el número total de QEMUs, CPUs, memoria y discos en todos los clústeres
        $total_qemus = VirtualMachineHistory::sum('cluster_qemus');
        $total_cpus = VirtualMachineHistory::sum('cluster_cpu');
        $total_memorys = VirtualMachineHistory::sum('cluster_memory');
    
        $total_disks = VirtualMachineHistory::sum('cluster_disk');

        // Calculate the sum of QEMUs, CPUs, memory, and disks for each date
        $histories = VirtualMachineHistory::selectRaw('date, SUM(cluster_qemus) as cluster_qemus, SUM(cluster_cpu) as cluster_cpu, SUM(cluster_memory) as cluster_memory, SUM(cluster_disk) as cluster_disk')
            ->groupBy('date')
            ->get();

        return view('proxmox.virtualMachineHistoryAnual', compact('histories'), compact('total_qemus', 'total_cpus', 'total_memorys', 'total_disks', 'VMHistory'));
    }
}
