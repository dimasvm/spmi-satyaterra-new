<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('quality_standards')
            ->orderBy('id')
            ->get()
            ->each(function (object $standard): void {
                $statement = DB::table('standard_statements')->updateOrInsert(
                    [
                        'quality_standard_id' => $standard->id,
                        'code' => 'PS-001',
                    ],
                    [
                        'statement' => $standard->statement ?: ($standard->description ?: $standard->name),
                        'sort_order' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );

                $statementId = DB::table('standard_statements')
                    ->where('quality_standard_id', $standard->id)
                    ->where('code', 'PS-001')
                    ->value('id');

                DB::table('standard_indicators')
                    ->where('quality_standard_id', $standard->id)
                    ->whereNull('standard_statement_id')
                    ->update([
                        'standard_statement_id' => $statementId,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('standard_indicators')
            ->whereIn('standard_statement_id', DB::table('standard_statements')->select('id')->where('code', 'PS-001'))
            ->update([
                'standard_statement_id' => null,
                'updated_at' => now(),
            ]);

        DB::table('standard_statements')
            ->where('code', 'PS-001')
            ->delete();
    }
};
