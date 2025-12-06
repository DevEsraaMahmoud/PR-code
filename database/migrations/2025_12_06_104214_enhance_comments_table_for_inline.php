<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Enhances comments table to support both regular and inline comments.
     * Inline comments are tied to code blocks via snippet_id and line ranges.
     * Regular comments are tied directly to posts (post_id).
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Add post_id for regular (non-inline) comments
            $table->foreignId('post_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            
            // Make snippet_id nullable (for regular comments)
            $table->foreignId('snippet_id')->nullable()->change();
            
            // Add is_inline flag
            $table->boolean('is_inline')->default(false)->after('snippet_id');
            
            // Make line numbers nullable (for regular comments)
            $table->integer('start_line')->nullable()->change();
            $table->integer('end_line')->nullable()->change();
            
            // Add edited_at timestamp
            $table->timestamp('edited_at')->nullable()->after('updated_at');
            
            // Add indexes for performance
            $table->index(['post_id', 'created_at']);
            $table->index(['snippet_id', 'is_inline']);
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->dropColumn(['post_id', 'is_inline', 'edited_at']);
            $table->dropIndex(['post_id', 'created_at']);
            $table->dropIndex(['snippet_id', 'is_inline']);
            $table->dropIndex(['parent_id']);
            
            // Revert nullable changes
            $table->foreignId('snippet_id')->nullable(false)->change();
            $table->integer('start_line')->nullable(false)->change();
            $table->integer('end_line')->nullable(false)->change();
        });
    }
};
