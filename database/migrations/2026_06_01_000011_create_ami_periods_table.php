<?php

use App\Enums\AmiPeriodStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ami_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spmi_period_id')->constrained('spmi_periods')->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default(AmiPeriodStatus::Draft->value);
            $table->timestamps();

            $table->index(['spmi_period_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ami_periods');
    }
};
