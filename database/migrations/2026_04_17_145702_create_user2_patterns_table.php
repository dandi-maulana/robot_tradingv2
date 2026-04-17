<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user2_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('market', 100);
            $table->date('tanggal');
            $table->string('waktu_block', 10); // e.g. "10:00"
            $table->string('c1', 20)->nullable(); // Merah / Hijau / Doji/Merah / Doji/Hijau
            $table->string('c2', 20)->nullable();
            $table->string('c3', 20)->nullable();
            $table->string('c4', 20)->nullable();
            $table->string('c5', 20)->nullable();
            $table->string('pattern_type', 10)->default('NONE'); // UP / DOWN / NONE
            $table->boolean('notif_sent')->default(false);
            $table->timestamps();

            $table->unique(['market', 'tanggal', 'waktu_block'], 'unique_market_block');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user2_patterns');
    }
};
