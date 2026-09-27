<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasas_cambio', function (Blueprint $table) {
            $table->id();
            $table->string('moneda_origen', 5);
            $table->string('moneda_destino', 5);
            $table->decimal('tasa', 15, 6);
            $table->timestamp('fecha_consulta')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tasas_cambio'); }
};