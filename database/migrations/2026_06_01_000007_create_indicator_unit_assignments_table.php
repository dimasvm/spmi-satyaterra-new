<?php

use App\Enums\IndicatorAssignmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_unit_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_indicator_id')->constrained('standard_indicators')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->foreignId('spmi_period_id')->constrained('spmi_periods')->cascadeOnDelete();
            $table->date('due_date')->nullable();
            $table->string('status')->default(IndicatorAssignmentStatus::Assigned->value);
            $table->timestamps();

            $table->unique(['standard_indicator_id', 'unit_id', 'spmi_period_id'], 'indicator_unit_period_unique');
            $table->index(['unit_id', 'status']);
            $table->index(['spmi_period_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_unit_assignments');
    }
};
