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
            if (!Schema::hasColumn('movies', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('tmdb_id')->comment('FK to categories.id');
                // Add foreign key only if categories table exists
                if (Schema::hasTable('categories')) {
                    $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'category_id')) {
                // Drop foreign key if it exists
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails($table->getTable());
                if ($doctrineTable->hasForeignKey('movies_category_id_foreign')) {
                    $table->dropForeign('movies_category_id_foreign');
                }
                $table->dropColumn('category_id');
            }
        });
    }
};
