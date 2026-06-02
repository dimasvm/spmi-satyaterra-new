<?php

use App\Enums\SpmiPeriodStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spmi_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year')->nullable(); // contoh: 2025/2026
            $table->string('semester')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default(SpmiPeriodStatus::Draft->value);
            $table->timestamps();

            $table->index(['academic_year', 'semester']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spmi_periods');
    }
};
