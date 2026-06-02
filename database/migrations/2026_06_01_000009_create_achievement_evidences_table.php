<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievement_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_achievement_id')->constrained('indicator_achievements')->cascadeOnDelete();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->string('external_url')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('indicator_achievement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievement_evidences');
    }
};
