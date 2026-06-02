<?php

use App\Enums\QualityStandardStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standard_category_id')->constrained('standard_categories')->cascadeOnDelete();
            $table->foreignId('spmi_period_id')->nullable()->constrained('spmi_periods')->nullOnDelete();
            $table->string('code');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('status')->default(QualityStandardStatus::Draft->value);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['spmi_period_id', 'code']);
            $table->index(['standard_category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_standards');
    }
};
