<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('publication_student');

        Schema::create('publication_student', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('publication_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'publication_id']);
            $table->index('student_id');
            $table->index('publication_id');
        });

        if (Schema::hasColumn('students', 'publication_id')) {
            foreach (DB::table('students')->whereNotNull('publication_id')->orderBy('id')->get() as $student) {
                DB::table('publication_student')->insertOrIgnore([
                    'student_id' => $student->id,
                    'publication_id' => $student->publication_id,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('students', function (Blueprint $table) {
                $table->dropConstrainedForeignId('publication_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('students', 'publication_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unsignedBigInteger('publication_id')->nullable()->after('thesis_title');
                $table->index('publication_id');
            });
        }

        foreach (DB::table('publication_student')->orderBy('student_id')->orderBy('sort_order')->get() as $row) {
            DB::table('students')
                ->where('id', $row->student_id)
                ->whereNull('publication_id')
                ->update(['publication_id' => $row->publication_id]);
        }

        Schema::dropIfExists('publication_student');
    }
};
