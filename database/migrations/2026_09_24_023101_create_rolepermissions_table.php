<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rolepermissions', function (Blueprint $table) {
            $table->id(); // Id (int, auto-increment, primary key)
            $table->unsignedBigInteger('access_right_id');
            $table->string('access_right_name', 300);
            $table->unsignedBigInteger('menu_id');
            $table->string('menu_name', 300);
            $table->unsignedInteger('menu_sort')->default(0);
            $table->unsignedBigInteger('sub_menu_id')->nullable();
            $table->string('sub_menu_name', 300)->nullable();
            $table->unsignedInteger('sub_menu_sort')->default(0);
            $table->string('record_status', 15)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rolepermissions');
    }
};