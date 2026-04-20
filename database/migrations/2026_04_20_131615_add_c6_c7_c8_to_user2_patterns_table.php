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
        Schema::table('user2_patterns', function (Blueprint $table) {
            if (!Schema::hasColumn('user2_patterns', 'c6')) {
                $table->string('c6', 20)->nullable()->after('c5');
            }
            if (!Schema::hasColumn('user2_patterns', 'c7')) {
                $table->string('c7', 20)->nullable()->after('c6');
            }
            if (!Schema::hasColumn('user2_patterns', 'c8')) {
                $table->string('c8', 20)->nullable()->after('c7');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user2_patterns', function (Blueprint $table) {
            $table->dropColumn(['c6', 'c7', 'c8']);
        });
    }
};
