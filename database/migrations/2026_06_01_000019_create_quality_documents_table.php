<?php

use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_standard_id')->nullable()->constrained('quality_standards')->nullOnDelete();
            $table->foreignId('spmi_period_id')->nullable()->constrained('spmi_periods')->nullOnDelete();
            $table->string('title');
            $table->string('document_type')->default(QualityDocumentType::Other->value);
            $table->string('document_number')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->string('status')->default(QualityDocumentStatus::Draft->value);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'status']);
            $table->index(['spmi_period_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_documents');
    }
};
