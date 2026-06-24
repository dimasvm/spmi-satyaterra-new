<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_indicators', function (Blueprint $table) {
            $table->foreignId('standard_statement_id')
                ->nullable()
                ->after('quality_standard_id')
                ->constrained('standard_statements')
                ->nullOnDelete();

            $table->index(['standard_statement_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('standard_indicators', function (Blueprint $table) {
            $table->dropIndex(['standard_statement_id', 'code']);
            $table->dropConstrainedForeignId('standard_statement_id');
        });
    }
};
