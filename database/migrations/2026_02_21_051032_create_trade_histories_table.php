<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('trade_histories', function (Blueprint $table) {
            $table->id();
            $table->string('tanggal', 20);
            $table->string('waktu', 20);
            $table->string('market', 100);
            $table->string('warna', 50);
            $table->float('amount');
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('trade_histories');
    }
};