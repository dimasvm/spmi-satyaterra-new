<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrective_action_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corrective_action_id')->constrained('corrective_actions')->cascadeOnDelete();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('corrective_action_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrective_action_evidences');
    }
};
