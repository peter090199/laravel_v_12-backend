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
        Schema::create('submenus', function (Blueprint $table) {
            $table->id();
            $table->string('sub_menu_name', 300);
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->string('menu_name', 300)->nullable();
            $table->string('record_status', 15)->nullable();
            $table->timestamps();

            $table->unique(['sub_menu_name', 'menu_id'], 'sub_menus_sub_menu_name_menu_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submenus');
    }
};