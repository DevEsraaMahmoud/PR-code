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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('bookmarkable'); // bookmarkable_id, bookmarkable_type
            $table->string('folder_name')->nullable(); // Optional folder organization
            $table->timestamps();

            $table->unique(['user_id', 'bookmarkable_id', 'bookmarkable_type']);
            $table->index(['user_id', 'folder_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};


