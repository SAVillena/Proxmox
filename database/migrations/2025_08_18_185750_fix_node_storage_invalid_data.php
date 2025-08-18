<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Simplemente truncar la tabla node_storage ya que tiene datos corruptos
        // Los datos se regenerarán la próxima vez que se ejecute la sincronización
        DB::statement("TRUNCATE TABLE node_storage RESTART IDENTITY CASCADE");
        
        echo "Tabla node_storage limpiada. Los datos se regenerarán en la próxima sincronización.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay reversión necesaria ya que solo eliminamos datos incorrectos
        echo "No se requiere reversión para la limpieza de datos inválidos.\n";
    }
};
