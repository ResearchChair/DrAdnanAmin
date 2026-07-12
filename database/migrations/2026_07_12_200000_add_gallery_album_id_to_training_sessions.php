<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('gallery_album_id')->nullable()->after('materials_url');
            $table->index('gallery_album_id');
        });
    }

    public function down(): void
    {
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropIndex(['gallery_album_id']);
            $table->dropColumn('gallery_album_id');
        });
    }
};
