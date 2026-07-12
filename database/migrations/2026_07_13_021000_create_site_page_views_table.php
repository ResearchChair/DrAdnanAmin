<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('site_page_views');

        Schema::create('site_page_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_key')->index();
            $table->string('path', 191)->index();
            $table->string('page_label', 80)->nullable();
            $table->string('country_code', 8)->nullable()->index();
            $table->string('country_name', 100)->nullable();
            $table->boolean('is_new_visitor')->default(false)->index();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_page_views');
    }
};
