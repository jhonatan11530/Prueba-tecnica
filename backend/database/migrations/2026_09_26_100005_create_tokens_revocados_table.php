<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create("tokens_revocados", function (Blueprint $table) {
            $table->id();
            $table->foreignId("usuario_id")->constrained("usuarios")->onDelete("cascade");
            $table->string("jti")->unique();
            $table->string("refresh_token_hash")->nullable();
            $table->boolean("revocado")->default(false);
            $table->timestamp("expires_at")->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists("tokens_revocados"); }
};
