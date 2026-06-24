<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('standard_categories')
                ->nullOnDelete();

            $table->index(['parent_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('standard_categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id', 'code']);
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
