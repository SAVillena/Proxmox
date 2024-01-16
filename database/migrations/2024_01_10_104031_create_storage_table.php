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
        Schema::create('storages', function (Blueprint $table) {
            $table->string('id_proxmox')->primary();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->bigInteger('disk')->nullable();
            $table->bigInteger('maxdisk')->nullable();
            $table->string('node_id')->nullable();
            $table->string('storage')->nullable();
            $table->string('content')->nullable();
            $table->string('plugintype')->nullable();
            $table->string('shared')->nullable();
            $table->float('used')->nullable();
            $table->timestamps();

            $table->foreign('node_id')->references('id_proxmox')->on('nodes')->onDelete('cascade');
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('storages');
    } 
};
