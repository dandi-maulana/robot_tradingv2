<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('market_histories', function (Blueprint $table) {
            $table->id();
            $table->string('market', 100);
            $table->string('tanggal', 20);
            $table->string('waktu', 20);
            $table->string('warna', 50);
            $table->timestamps();
            
            // Index untuk mempercepat query pencarian data oleh Python
            $table->index(['market', 'id']); 
        });
    }
    public function down() {
        Schema::dropIfExists('market_histories');
    }
};