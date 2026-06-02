<?php

use App\Enums\AmiFindingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ami_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ami_audit_id')->constrained('ami_audits')->cascadeOnDelete();
            $table->foreignId('ami_checklist_id')->nullable()->constrained('ami_checklists')->nullOnDelete();
            $table->foreignId('standard_indicator_id')->nullable()->constrained('standard_indicators')->nullOnDelete();
            $table->string('finding_number')->nullable();
            $table->string('category');
            $table->longText('description');
            $table->text('root_cause')->nullable();
            $table->longText('recommendation')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default(AmiFindingStatus::Open->value);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ami_audit_id', 'status']);
            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ami_findings');
    }
};
