<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('movies', 'dashboard_id')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->unsignedInteger('dashboard_id')->nullable()->after('visibility')->comment('Optional dashboard identifier to group movies for different dashboards');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('movies', 'dashboard_id')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropColumn('dashboard_id');
            });
        }
    }
};
