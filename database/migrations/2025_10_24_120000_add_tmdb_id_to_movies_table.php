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
        if (!Schema::hasColumn('movies', 'tmdb_id')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->unsignedBigInteger('tmdb_id')->nullable()->after('dashboard_id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('movies', 'tmdb_id')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropColumn('tmdb_id');
            });
        }
    }
};
