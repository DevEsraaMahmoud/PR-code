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
        Schema::table('users', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('email');
            $table->json('skills')->nullable()->after('bio'); // Array of skills
            $table->string('location')->nullable()->after('skills');
            $table->string('website')->nullable()->after('location');
            $table->string('github_username')->nullable()->after('website');
            $table->string('avatar_url')->nullable()->after('github_username');
            $table->string('theme_preference')->default('dark')->after('avatar_url'); // dark, light
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio',
                'skills',
                'location',
                'website',
                'github_username',
                'avatar_url',
                'theme_preference',
            ]);
        });
    }
};


