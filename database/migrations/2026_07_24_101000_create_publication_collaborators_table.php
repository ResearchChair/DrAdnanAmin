<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('publication_collaborators')) {
            return;
        }

        $publicationIdIsInt = false;
        $canUseForeignKey = true;

        if (DB::getDriverName() === 'mysql') {
            $column = DB::selectOne(
                "SELECT COLUMN_TYPE
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'publications'
                   AND COLUMN_NAME = 'id'
                 LIMIT 1"
            );

            $engine = DB::selectOne(
                "SELECT ENGINE
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'publications'
                 LIMIT 1"
            );

            $columnType = strtolower((string) ($column->COLUMN_TYPE ?? ''));
            $publicationIdIsInt = str_contains($columnType, 'int(') && ! str_contains($columnType, 'bigint(');
            $canUseForeignKey = strtoupper((string) ($engine->ENGINE ?? 'INNODB')) === 'INNODB';
        }

        Schema::create('publication_collaborators', function (Blueprint $table) use ($publicationIdIsInt, $canUseForeignKey) {
            $table->id();
            if ($publicationIdIsInt) {
                $table->unsignedInteger('publication_id');
            } else {
                $table->unsignedBigInteger('publication_id');
            }
            $table->string('email');
            $table->string('token_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['publication_id', 'email']);
            $table->index('email');

            if ($canUseForeignKey) {
                $table->foreign('publication_id')
                    ->references('id')
                    ->on('publications')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publication_collaborators');
    }
};
