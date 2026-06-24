<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->string('scope_type')->nullable()->after('standard_category_id');
            $table->longText('statement')->nullable()->after('name');

            $table->index(['scope_type', 'standard_category_id', 'status'], 'quality_standards_scope_category_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->dropIndex('quality_standards_scope_category_status_index');
            $table->dropColumn(['scope_type', 'statement']);
        });
    }
};
