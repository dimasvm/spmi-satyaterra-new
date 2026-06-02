<?php

use App\Enums\AmiAuditStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ami_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ami_period_id')->constrained('ami_periods')->cascadeOnDelete();
            $table->foreignId('auditee_unit_id')->constrained('units')->cascadeOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->string('status')->default(AmiAuditStatus::Planned->value);
            $table->text('notes')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['ami_period_id', 'auditee_unit_id'], 'ami_period_auditee_unique');
            $table->index(['auditee_unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ami_audits');
    }
};
