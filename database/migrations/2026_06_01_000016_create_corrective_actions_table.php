<?php

use App\Enums\CorrectiveActionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ami_finding_id')->constrained('ami_findings')->cascadeOnDelete();
            $table->longText('action_plan');
            $table->longText('root_cause_analysis')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date')->nullable();
            $table->string('status')->default(CorrectiveActionStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ami_finding_id', 'status']);
            $table->index('pic_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corrective_actions');
    }
};
