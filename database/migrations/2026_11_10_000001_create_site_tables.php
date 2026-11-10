<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single values: home text, contact details, images, footer links
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->longText('value')->nullable();
        });

        // Music page: albums with audio tracks
        Schema::create('albums', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('tracks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('album_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->string('file');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        // Music page: "Music Video" grid. type = youtube (source = video id) or file (source = path)
        Schema::create('music_videos', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('subtitle')->nullable();
            $t->string('type', 10);
            $t->string('source');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        // Visual Art and Theatre galleries (section = visual-art | theatre)
        Schema::create('gallery_images', function (Blueprint $t) {
            $t->id();
            $t->string('section', 30)->index();
            $t->string('image');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        // Portfolio: groups of photos
        Schema::create('portfolio_groups', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('portfolio_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('portfolio_group_id')->constrained()->cascadeOnDelete();
            $t->string('image');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        // About Me: text blocks, each on the left or right column
        Schema::create('about_blocks', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->longText('body')->nullable();
            $t->string('side', 5)->default('left');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['about_blocks', 'portfolio_images', 'portfolio_groups', 'gallery_images', 'music_videos', 'tracks', 'albums', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
