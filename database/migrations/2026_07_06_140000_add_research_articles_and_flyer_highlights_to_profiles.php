<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->longText('research_articles_html')->nullable()->after('research_interests');
            $table->json('flyer_highlights')->nullable()->after('research_articles_html');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['research_articles_html', 'flyer_highlights']);
        });
    }
};
