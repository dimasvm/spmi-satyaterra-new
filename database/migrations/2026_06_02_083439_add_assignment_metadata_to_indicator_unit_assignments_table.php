<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicator_unit_assignments', function (Blueprint $table) {
            $table->boolean('is_primary_pic')->default(false)->after('status');
            $table->string('priority')->default('normal')->after('is_primary_pic');
            $table->text('notes')->nullable()->after('priority');
            $table->foreignId('assigned_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');

            $table->index(['standard_indicator_id', 'spmi_period_id', 'is_primary_pic'], 'indicator_assignment_pic_index');
            $table->index(['priority', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicator_unit_assignments', function (Blueprint $table) {
            $table->dropIndex('indicator_assignment_pic_index');
            $table->dropIndex(['priority', 'status']);
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn([
                'is_primary_pic',
                'priority',
                'notes',
                'assigned_at',
            ]);
        });
    }
};
