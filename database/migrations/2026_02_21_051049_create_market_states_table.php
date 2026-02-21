<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('market_states', function (Blueprint $table) {
            $table->id();
            $table->string('market', 100)->unique();
            $table->boolean('is_running')->default(false);
            $table->integer('total_trade')->default(0);
            $table->integer('total_hijau')->default(0);
            $table->integer('total_merah')->default(0);
            // Konfigurasi Telegram
            $table->boolean('tg_active')->default(false);
            $table->integer('tg_target_loss')->default(7);
            $table->string('tg_phase', 20)->default('IDLE');
            $table->integer('tg_trade_counter')->default(0);
            $table->string('tg_last_candle', 100)->nullable();
            $table->string('tg_direction', 20)->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('market_states');
    }
};