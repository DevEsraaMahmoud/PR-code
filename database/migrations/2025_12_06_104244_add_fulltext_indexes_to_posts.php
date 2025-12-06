<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fulltext indexes for search functionality.
     * Note: MySQL fulltext indexes require MyISAM or InnoDB with innodb_ft_min_token_size configured.
     */
    public function up(): void
    {
        // For MySQL: Add fulltext index on title and body
        // Note: JSON columns need special handling - we'll search the JSON content
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_title_fulltext (title)');
            // For JSON body, we'll need to extract text content in application layer
            // or use a generated column if MySQL 5.7+
        }
        
        // Add regular indexes for common queries
        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('slug');
            $table->index('visibility');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'slug', 'visibility', 'created_at']);
        });
        
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE posts DROP INDEX posts_title_fulltext');
        }
    }
};
