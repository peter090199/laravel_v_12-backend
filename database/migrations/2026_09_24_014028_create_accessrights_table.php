<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessrights', function (Blueprint $table) {
            $table->id(); // int, auto-increment, primary key
            $table->string('access_right_name', 300);
            $table->string('record_status', 15);
            $table->timestamps(); // created_at / updated_at, remove if not needed
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessrights');
    }
};