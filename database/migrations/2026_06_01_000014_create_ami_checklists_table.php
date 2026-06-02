<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ami_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ami_audit_id')->constrained('ami_audits')->cascadeOnDelete();
            $table->foreignId('standard_indicator_id')->constrained('standard_indicators')->cascadeOnDelete();
            $table->string('assessment_result')->nullable();
            $table->text('auditor_notes')->nullable();
            $table->timestamps();

            $table->unique(['ami_audit_id', 'standard_indicator_id'], 'ami_audit_indicator_unique');
            $table->index('assessment_result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ami_checklists');
    }
};
