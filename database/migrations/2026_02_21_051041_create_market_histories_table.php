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
            // Kolom Tambahan OHLCV
            $table->float('open_price')->nullable();
            $table->float('close_price')->nullable();
            $table->float('high_price')->nullable();
            $table->float('low_price')->nullable();
            $table->integer('tick_volume')->default(0); 
            $table->timestamps();
            
            $table->index(['market', 'id']); 
        });
    }

    public function down() {
        Schema::dropIfExists('market_histories');
    }
};