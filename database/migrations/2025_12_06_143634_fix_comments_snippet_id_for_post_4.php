<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix comments for post_id = 4
        // Comments are linked to snippet_id = 4, but snippet 4 doesn't exist
        // Snippet 6 is the first snippet (block_index: 0) for post 4
        // So we need to update comments from snippet_id = 4 to snippet_id = 6
        
        DB::statement("
            UPDATE comments 
            SET snippet_id = 6 
            WHERE post_id = 4 
            AND snippet_id = 4 
            AND is_inline = 1
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: update comments back to snippet_id = 4
        DB::statement("
            UPDATE comments 
            SET snippet_id = 4 
            WHERE post_id = 4 
            AND snippet_id = 6 
            AND is_inline = 1
        ");
    }
};
