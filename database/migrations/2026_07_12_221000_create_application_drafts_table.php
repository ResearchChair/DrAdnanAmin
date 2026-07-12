<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_drafts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('document_type', 40)->index();
            $table->string('position_title')->nullable();
            $table->string('institution')->nullable();
            $table->string('tone', 40)->default('formal');
            $table->text('job_text');
            $table->text('extra_notes')->nullable();
            $table->json('publication_ids')->nullable();
            $table->json('options')->nullable();
            $table->longText('output_markdown');
            $table->string('model')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_drafts');
    }
};
