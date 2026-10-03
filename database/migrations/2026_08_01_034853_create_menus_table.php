<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu_name', 300)->nullable();
            $table->string('record_status', 15)->nullable();
            $table->string('enterprise', 15)->nullable();
            $table->string('standard', 15)->nullable();
            $table->string('express', 15)->nullable();
            $table->string('project_enterprise', 50)->nullable();
            $table->string('project_standard', 50)->nullable();
            $table->string('project_express', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};