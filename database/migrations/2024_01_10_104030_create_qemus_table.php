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
    Schema::create('qemus', function (Blueprint $table) {
            $table->string('node_id')->nullable();
            $table->string('id_proxmox')->primary();
            $table->string('vmid')->nullable();
            $table->string('name')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->bigInteger('disk')->nullable();
            $table->bigInteger('maxdisk')->nullable();
            $table->bigInteger('uptime')->nullable();
            $table->bigInteger('mem')->nullable();
            $table->bigInteger('maxmem')->nullable();
            $table->float('cpu')->nullable();
            $table->integer('maxcpu')->nullable();
            $table->bigInteger('netin')->nullable();
            $table->bigInteger('netout')->nullable();
            $table->string('storageName')->nullable();
            $table->bigInteger('size')->nullable();
            $table->timestamps();

            $table->foreign('node_id')->references('id_proxmox')->on('nodes')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qemus');
    }
};
