<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campus_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('pddikti_id')->unique()->comment('Encrypted ID from PDDikti');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('npsn')->nullable();
            $table->string('accreditation')->nullable()->comment('e.g. A, B, C, Unggul, Baik Sekali');
            $table->string('status')->nullable()->comment('Aktif / Tidak Aktif');
            $table->string('type')->nullable()->comment('Universitas, Institut, etc.');
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('total_lecturers')->default(0);
            $table->unsignedInteger('total_study_programs')->default(0);
            $table->json('faculties')->nullable()->comment('Array of faculty with study programs');
            $table->json('student_stats')->nullable()->comment('Student count per study program for chart');
            $table->json('accreditation_stats')->nullable()->comment('Study program accreditation distribution');
            $table->json('raw_data')->nullable()->comment('Raw snapshot from PDDikti');
            $table->boolean('is_active')->default(false)->comment('Currently displayed on public page');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campus_profiles');
    }
};
