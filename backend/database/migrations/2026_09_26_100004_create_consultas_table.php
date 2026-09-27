<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("consultas", function (Blueprint $table) {
            $table->id();
            $table->foreignId("usuario_id")->constrained("usuarios")->onDelete("cascade");
            $table->foreignId("ciudad_id")->constrained("ciudades")->onDelete("cascade");
            $table->decimal("presupuesto_cop", 15, 2);
            $table->decimal("clima", 5, 2)->nullable();
            $table->decimal("tasa", 15, 6)->nullable();
            $table->decimal("valor_convertido", 15, 2)->nullable();
            $table->timestamp("fecha")->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("consultas"); }
};
