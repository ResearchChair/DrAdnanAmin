<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worked_with_organizations', function (Blueprint $table) {
            $table->boolean('show_title')->default(true)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('worked_with_organizations', function (Blueprint $table) {
            $table->dropColumn('show_title');
        });
    }
};
