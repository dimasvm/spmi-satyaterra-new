<?php

use App\Enums\StandardIndicatorType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_standard_id')->constrained('quality_standards')->cascadeOnDelete();
            $table->string('code');
            $table->text('statement');
            $table->string('indicator_type')->default(StandardIndicatorType::Percentage->value);
            $table->decimal('target_value', 12, 2)->nullable();
            $table->string('target_operator')->nullable();
            $table->string('target_unit')->nullable(); // %, dokumen, orang, kegiatan
            $table->unsignedInteger('weight')->default(1);
            $table->boolean('evidence_required')->default(true);
            $table->text('evidence_description')->nullable();
            $table->timestamps();

            $table->unique(['quality_standard_id', 'code']);
            $table->index(['indicator_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_indicators');
    }
};
