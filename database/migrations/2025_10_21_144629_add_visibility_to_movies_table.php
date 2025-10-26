<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (!Schema::hasColumn('movies', 'visibility')) {
                // values: 'dashboard' | 'homepage' | 'both'
                $table->string('visibility')->default('dashboard')->after('is_featured');
            }
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'visibility')) {
                $table->dropColumn('visibility');
            }
        });
    }
};
