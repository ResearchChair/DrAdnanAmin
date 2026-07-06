<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('name');
            $table->string('batch')->nullable()->after('degree');
            $table->foreignId('publication_id')->nullable()->after('thesis_title')->constrained('publications')->nullOnDelete();
            $table->json('profile_links')->nullable()->after('description');
            $table->date('completed_at')->nullable()->after('completion_year');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publication_id');
            $table->dropColumn(['photo_path', 'batch', 'profile_links', 'completed_at']);
        });
    }
};
