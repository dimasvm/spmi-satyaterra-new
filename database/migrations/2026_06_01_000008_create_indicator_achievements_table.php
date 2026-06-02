<?php

use App\Enums\SubmissionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('indicator_unit_assignments')->cascadeOnDelete();
            $table->decimal('realization_value', 12, 2)->nullable();
            $table->longText('realization_text')->nullable();
            $table->string('achievement_status')->nullable();
            $table->text('notes')->nullable();
            $table->string('submission_status')->default(SubmissionStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assignment_id', 'submission_status']);
            $table->index('achievement_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_achievements');
    }
};
