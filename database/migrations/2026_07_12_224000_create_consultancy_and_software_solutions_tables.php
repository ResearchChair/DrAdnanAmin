<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultancy_engagements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('organization');
            $table->string('role')->nullable();
            $table->string('type', 40)->default('advisory')->index();
            $table->unsignedSmallInteger('year_start')->nullable()->index();
            $table->unsignedSmallInteger('year_end')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('software_solutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('organization');
            $table->string('tagline')->nullable();
            $table->string('type', 40)->default('web_app')->index();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('tech_stack')->nullable();
            $table->string('url')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_solutions');
        Schema::dropIfExists('consultancy_engagements');
    }
};
