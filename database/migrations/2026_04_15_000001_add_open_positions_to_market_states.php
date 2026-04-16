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
        Schema::table('market_states', function (Blueprint $table) {
            // Tambah kolom untuk tracking open positions hari ini
            if (!Schema::hasColumn('market_states', 'open_positions_today')) {
                $table->integer('open_positions_today')->default(0)->comment('Jumlah open posisi hari ini (cumulative)');
            }

            // Tambah kolom untuk tracking tanggal terakhir kali reset
            if (!Schema::hasColumn('market_states', 'last_positions_reset_date')) {
                $table->date('last_positions_reset_date')->nullable()->comment('Tanggal terakhir kali open_positions_today direset');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_states', function (Blueprint $table) {
            $table->dropColumn('open_positions_today');
            $table->dropColumn('last_positions_reset_date');
        });
    }
};
