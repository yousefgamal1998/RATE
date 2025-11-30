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
        Schema::table('movies', function (Blueprint $table) {
            if (!Schema::hasColumn('movies', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('movies', 'year')) {
                $table->smallInteger('year')->nullable()->after('description');
            }
            if (!Schema::hasColumn('movies', 'duration')) {
                $table->string('duration')->nullable()->after('year');
            }
            if (!Schema::hasColumn('movies', 'genres')) {
                $table->json('genres')->nullable()->after('duration');
            }
            if (!Schema::hasColumn('movies', 'rating_decimal')) {
                $table->decimal('rating_decimal', 3, 1)->nullable()->after('rating');
            }
            if (!Schema::hasColumn('movies', 'image_path')) {
                $table->string('image_path')->nullable()->after('rating_decimal');
            }
            if (!Schema::hasColumn('movies', 'video_url')) {
                $table->string('video_url')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('movies', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('video_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $columns = ['slug','year','duration','genres','rating_decimal','image_path','video_url','is_featured'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('movies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
