<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_drafts', function (Blueprint $table) {
            $table->string('provider', 40)->nullable()->after('model');
        });
    }

    public function down(): void
    {
        Schema::table('application_drafts', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }
};
