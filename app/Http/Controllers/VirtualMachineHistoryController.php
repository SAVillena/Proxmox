<?php

namespace App\Http\Controllers;

use App\Models\MonthlyTotal;
use App\Models\VirtualMachineHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VirtualMachineHistoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view cluster');
    }
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

        //histories suma de todos los clusters del mes
        $histories = VirtualMachineHistory::selectRaw('date, SUM(cluster_qemus) as cluster_qemus, SUM(cluster_cpu) as cluster_cpu, SUM(cluster_memory) as cluster_memory, SUM(cluster_disk) as cluster_disk')
            ->groupBy('date')->get();

        $total_qemus = $histories->sum('cluster_qemus');
        $total_cpus = $histories->sum('cluster_cpu');
        $total_memorys = $histories->sum('cluster_memory');

        $total_disks = $histories->sum('cluster_disk');

        $growth = [
            'qemus' => 0,
            'cpus' => 0,
            'memorys' => 0,
            'disks' => 0,
        ];

        $lastRecord = MonthlyTotal::orderBy('date', 'desc')->first();
        $secondLastRecord = MonthlyTotal::orderBy('date', 'desc')->skip(1)->first();
        $lastMonthlyTotal = MonthlyTotal::orderBy('date', 'desc')->first();

        if ($lastRecord && $secondLastRecord) {
            $growth['qemus'] = $lastRecord->cluster_qemus - $secondLastRecord->cluster_qemus;
            $growth['cpus'] = $lastRecord->cluster_cpu - $secondLastRecord->cluster_cpu;
            $growth['memorys'] = $lastRecord->cluster_memory - $secondLastRecord->cluster_memory;
            $growth['disks'] = $lastRecord->cluster_disk - $secondLastRecord->cluster_disk;
        } else {
            $growth['qemus'] = 0;
            $growth['cpus'] = 0;
            $growth['memorys'] = 0;
            $growth['disks'] = 0;
        }

        return view('proxmox.virtualMachineHistory', compact('histories'), compact('total_qemus', 'total_cpus', 'total_memorys', 'total_disks', 'VMHistory', 'growth', 'lastMonthlyTotal'));
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


        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;

        // Obtener todos los registros del año actual y del año anterior
        $currentYearRecords = MonthlyTotal::whereYear('date', $currentYear)->orderBy('date')->get();
        
        //obtener la diferencia de la suma del año actual
        $last = $currentYearRecords->last();
        $currentYearSumQemu = $last ? $last->cluster_qemus : 0;
        $currentYearSumCPU = $last ? $last->cluster_cpu : 0;
        $currentYearSumRAM = $last ? $last->cluster_memory : 0;
        $currentYearSumDisk = $last ? $last->cluster_disk : 0;
        //obtener el ultimo registro del año anterior
        $previousYearRecord = MonthlyTotal::whereYear('date', $previousYear)->orderBy('date', 'desc')->first();
        
        $growth = [
            'qemus' => 0,
            'cpus' => 0,
            'memorys' => 0,
            'disks' => 0,
        ];

        if($previousYearRecord){
            
            //restar la suma del año actual menos el ultimo registro del año anterior
            $growth['qemus'] = $currentYearSumQemu - $previousYearRecord->cluster_qemus;
            $growth['cpus'] = $currentYearSumCPU - $previousYearRecord->cluster_cpu;
            $growth['memorys'] = $currentYearSumRAM - $previousYearRecord->cluster_memory;
            $growth['disks'] = $currentYearSumDisk - $previousYearRecord->cluster_disk;
            
        }

        

        $lastRecord = MonthlyTotal::orderBy('date', 'desc')->first();
        return view('proxmox.virtualMachineHistoryAnual', compact('histories'), compact('total_qemus', 'total_cpus', 'total_memorys', 'total_disks', 'VMHistory', 'growth'));
    }
}
