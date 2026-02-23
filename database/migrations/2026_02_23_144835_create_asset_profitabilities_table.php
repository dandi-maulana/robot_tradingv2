<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('asset_profitabilities', function (Blueprint $table) {
            $table->id();
            $table->string('market', 100)->unique();
            $table->integer('payout')->default(0); 
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('asset_profitabilities');
    }
};