<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_standard_id')->constrained('quality_standards')->cascadeOnDelete();
            $table->string('code');
            $table->longText('statement');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['quality_standard_id', 'code']);
            $table->index(['quality_standard_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_statements');
    }
};
