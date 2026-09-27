<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("paises", function (Blueprint $table) {
            $table->id();
            $table->string("nombre");
            $table->string("codigo", 10)->unique();
            $table->foreignId("moneda_id")->constrained("monedas")->onDelete("cascade");
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("paises"); }
};
