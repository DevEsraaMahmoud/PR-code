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
        Schema::table('posts', function (Blueprint $table) {
            $table->string('slug')->unique()->after('title');
            $table->enum('visibility', ['public', 'private', 'unlisted'])->default('public')->after('body');
            $table->json('meta')->nullable()->after('visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['slug', 'visibility', 'meta']);
        });
    }
};
