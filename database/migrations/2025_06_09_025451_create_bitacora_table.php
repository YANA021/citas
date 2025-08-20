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
       Schema::create('bitacora', function (Blueprint $table) {
        $table->id();
        $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
        $table->text('accion');
        $table->string('ip')->nullable();
        $table->timestamp('fecha')->useCurrent();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
