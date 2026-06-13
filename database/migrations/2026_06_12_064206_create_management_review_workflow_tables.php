<?php

use App\Enums\ManagementReviewAttendanceStatus;
use App\Enums\ManagementReviewItemPriority;
use App\Enums\ManagementReviewItemStatus;
use App\Enums\ManagementReviewItemType;
use App\Enums\ManagementReviewStatus;
use App\Enums\StandardImprovementProposalStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('spmi_period_id')->nullable()->constrained('spmi_periods')->nullOnDelete();
            $table->foreignId('ami_period_id')->nullable()->constrained('ami_periods')->nullOnDelete();
            $table->string('title');
            $table->date('meeting_date')->nullable();
            $table->string('location')->nullable();
            $table->longText('agenda')->nullable();
            $table->longText('summary')->nullable();
            $table->longText('conclusion')->nullable();
            $table->string('status')->default(ManagementReviewStatus::Draft->value);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['spmi_period_id', 'status']);
            $table->index(['ami_period_id', 'status']);
        });

        Schema::create('management_review_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('management_review_id')->constrained('management_reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('attendance_status')->default(ManagementReviewAttendanceStatus::Present->value);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['management_review_id', 'attendance_status']);
        });

        Schema::create('management_review_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('management_review_id')->constrained('management_reviews')->cascadeOnDelete();
            $table->string('item_type')->default(ManagementReviewItemType::General->value);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->longText('analysis')->nullable();
            $table->longText('decision')->nullable();
            $table->longText('recommendation')->nullable();
            $table->string('priority')->default(ManagementReviewItemPriority::Medium->value);
            $table->string('status')->default(ManagementReviewItemStatus::Open->value);
            $table->timestamps();

            $table->index(['management_review_id', 'item_type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['priority', 'status']);
        });

        Schema::create('standard_improvement_proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('management_review_id')->nullable()->constrained('management_reviews')->nullOnDelete();
            $table->foreignId('quality_standard_id')->nullable()->constrained('quality_standards')->nullOnDelete();
            $table->foreignId('standard_indicator_id')->nullable()->constrained('standard_indicators')->nullOnDelete();
            $table->string('proposal_type');
            $table->string('title');
            $table->longText('background')->nullable();
            $table->longText('current_condition')->nullable();
            $table->longText('proposed_change');
            $table->longText('reason')->nullable();
            $table->longText('expected_impact')->nullable();
            $table->decimal('proposed_target_value', 12, 2)->nullable();
            $table->string('proposed_target_operator')->nullable();
            $table->string('proposed_target_unit')->nullable();
            $table->longText('proposed_indicator_statement')->nullable();
            $table->string('proposed_standard_name')->nullable();
            $table->longText('proposed_standard_description')->nullable();
            $table->foreignId('target_spmi_period_id')->nullable()->constrained('spmi_periods')->nullOnDelete();
            $table->string('status')->default(StandardImprovementProposalStatus::Draft->value);
            $table->foreignId('proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('implemented_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('implemented_at')->nullable();
            $table->foreignId('created_standard_id')->nullable()->constrained('quality_standards')->nullOnDelete();
            $table->foreignId('created_indicator_id')->nullable()->constrained('standard_indicators')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'proposal_type']);
            $table->index(['target_spmi_period_id', 'status']);
        });

        Schema::create('standard_revision_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quality_standard_id')->nullable()->constrained('quality_standards')->nullOnDelete();
            $table->foreignId('standard_indicator_id')->nullable()->constrained('standard_indicators')->nullOnDelete();
            $table->foreignId('standard_improvement_proposal_id')->nullable()->constrained('standard_improvement_proposals')->nullOnDelete();
            $table->string('revision_type');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revised_at')->nullable();
            $table->timestamps();

            $table->index(['quality_standard_id', 'revision_type']);
            $table->index(['standard_indicator_id', 'revision_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_revision_histories');
        Schema::dropIfExists('standard_improvement_proposals');
        Schema::dropIfExists('management_review_items');
        Schema::dropIfExists('management_review_participants');
        Schema::dropIfExists('management_reviews');
    }
};
