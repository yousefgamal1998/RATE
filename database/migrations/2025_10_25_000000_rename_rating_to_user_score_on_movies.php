<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameRatingToUserScoreOnMovies extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: Renaming columns using the schema builder requires the doctrine/dbal package
     * in many Laravel installations. If you get an error when running this migration,
     * run:
     *
     *   composer require doctrine/dbal
     *
     * Then re-run: php artisan migrate
     *
     * This migration renames the existing integer `rating` column to `user_score`.
     * The `rating_decimal` column is left untouched.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('movies') && Schema::hasColumn('movies', 'rating')) {
            Schema::table('movies', function (Blueprint $table) {
                // Use renameColumn; may require doctrine/dbal
                $table->renameColumn('rating', 'user_score');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('movies') && Schema::hasColumn('movies', 'user_score')) {
            Schema::table('movies', function (Blueprint $table) {
                $table->renameColumn('user_score', 'rating');
            });
        }
    }
}
