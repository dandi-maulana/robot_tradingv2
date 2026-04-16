<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phase_histories', function (Blueprint $table) {
            $table->id();
            $table->string('market', 100);
            $table->unsignedInteger('target_loss')->default(2);
            $table->string('tanggal', 20);
            $table->string('waktu', 20);
            $table->string('phase_1', 10)->default('-');
            $table->string('phase_2', 10)->default('-');
            $table->string('phase_3', 10)->default('-');
            $table->string('phase_4', 10)->default('-');
            $table->string('phase_5', 10)->default('-');
            $table->string('phase_6', 10)->default('-');
            $table->string('phase_7', 10)->default('-');
            $table->string('resolved_result', 20)->default('PENDING');
            $table->unsignedInteger('resolved_phase')->nullable();
            $table->string('trigger_at', 25);
            $table->string('resolved_at', 25)->nullable();
            $table->timestamps();

            $table->unique(['market', 'target_loss', 'trigger_at'], 'phase_histories_unique_signal');
            $table->index(['target_loss', 'tanggal'], 'phase_histories_target_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phase_histories');
    }
};
