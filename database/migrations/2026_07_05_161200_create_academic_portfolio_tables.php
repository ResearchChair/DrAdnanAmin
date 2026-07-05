<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('credentials')->nullable();
            $table->string('title')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('secondary_affiliation')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->text('tagline')->nullable();
            $table->longText('bio_html')->nullable();
            $table->longText('research_interests')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('orcid_id')->nullable();
            $table->string('openalex_author_id')->nullable();
            $table->timestamps();
        });

        Schema::create('citation_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_citations')->default(0);
            $table->unsignedInteger('h_index')->default(0);
            $table->unsignedInteger('i10_index')->default(0);
            $table->unsignedInteger('publication_count')->default(0);
            $table->string('source')->default('google_scholar');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('label')->nullable();
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('platform');
            $table->string('label')->nullable();
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('journal');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('venue')->nullable();
            $table->text('authors')->nullable();
            $table->string('doi')->nullable()->index();
            $table->string('url')->nullable();
            $table->string('pdf_url')->nullable();
            $table->text('abstract')->nullable();
            $table->unsignedInteger('citation_count')->default(0);
            $table->string('external_id_orcid')->nullable();
            $table->string('external_id_openalex')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('research_activities', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->string('organization')->nullable();
            $table->string('role')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('year_end')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('in_progress');
            $table->string('degree')->nullable();
            $table->string('thesis_title');
            $table->string('co_supervisors')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('completion_year')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('workshop');
            $table->string('event_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('role')->default('Resource Person');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('materials_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('gallery_albums', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('gallery_albums');
        Schema::dropIfExists('training_sessions');
        Schema::dropIfExists('students');
        Schema::dropIfExists('research_activities');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('academic_profiles');
        Schema::dropIfExists('citation_stats');
        Schema::dropIfExists('profiles');
    }
};
