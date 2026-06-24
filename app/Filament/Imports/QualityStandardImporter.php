<?php

namespace App\Filament\Imports;

use App\Enums\QualityStandardStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Enums\UnitType;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\StandardCategory;
use App\Models\StandardIndicator;
use App\Models\StandardStatement;
use App\Models\User;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class QualityStandardImporter implements OnEachRow, SkipsEmptyRows, WithHeadingRow
{
    private int $createdStandardsCount = 0;

    private int $updatedStandardsCount = 0;

    private int $createdIndicatorsCount = 0;

    private int $updatedIndicatorsCount = 0;

    public function __construct(private readonly User $user) {}

    public function onRow(Row $row): void
    {
        $this->processRow($row->toArray(), $row->getIndex());
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function processRow(array $row, int $rowIndex = 0): void
    {
        try {
            $data = $this->validatedData($this->normalizeRow($row));
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->map(fn ($msg) => "Baris {$rowIndex}: {$msg}")->toArray();
            throw ValidationException::withMessages(['file' => $messages]);
        }

        DB::transaction(function () use ($data): void {
            $category = $this->resolveStandardCategory($data);
            $spmiPeriodId = $this->resolveSpmiPeriodId($data);

            $standard = QualityStandard::query()->firstOrNew([
                'code' => $data['standard_code'],
                'spmi_period_id' => $spmiPeriodId,
            ]);

            $this->authorizeStandardImport($standard);

            $standard->fill([
                'standard_category_id' => $category->getKey(),
                'scope_type' => $data['scope_type'],
                'spmi_period_id' => $spmiPeriodId,
                'code' => $data['standard_code'],
                'name' => $data['standard_name'],
                'statement' => $data['standard_statement'],
                'description' => $data['standard_description'],
                'status' => $data['standard_status'],
                'version' => $data['standard_version'],
            ]);

            $standard->exists ? $this->updatedStandardsCount++ : $this->createdStandardsCount++;
            $standard->save();

            $statement = $this->resolveStandardStatement($standard, $data);

            $indicator = StandardIndicator::query()->firstOrNew([
                'quality_standard_id' => $standard->getKey(),
                'code' => $data['indicator_code'],
            ]);

            $this->authorizeIndicatorImport($indicator);

            $indicator->fill([
                'standard_statement_id' => $statement->getKey(),
                'statement' => $data['indicator_statement'],
                'indicator_type' => $data['indicator_type'],
                'target_operator' => $data['target_operator'],
                'target_value' => $data['target_value'],
                'target_unit' => $data['target_unit'],
                'weight' => $data['weight'],
                'evidence_required' => $data['evidence_required'],
                'evidence_description' => $data['evidence_description'],
            ]);

            $indicator->exists ? $this->updatedIndicatorsCount++ : $this->createdIndicatorsCount++;
            $indicator->save();
        });
    }

    public function getCreatedStandardsCount(): int
    {
        return $this->createdStandardsCount;
    }

    public function getUpdatedStandardsCount(): int
    {
        return $this->updatedStandardsCount;
    }

    public function getCreatedIndicatorsCount(): int
    {
        return $this->createdIndicatorsCount;
    }

    public function getUpdatedIndicatorsCount(): int
    {
        return $this->updatedIndicatorsCount;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $data = [];

        foreach ($row as $key => $value) {
            $data[Str::snake(trim((string) $key))] = is_string($value) ? trim($value) : $value;
        }

        $data['standard_status'] = $this->normalizeEnumValue(
            $data['standard_status'] ?? null,
            QualityStandardStatus::class,
            QualityStandardStatus::Draft->value,
        );

        $data['indicator_type'] = $this->normalizeEnumValue(
            $data['indicator_type'] ?? null,
            StandardIndicatorType::class,
            StandardIndicatorType::Percentage->value,
        );

        $data['target_operator'] = $this->normalizeEnumValue(
            $data['target_operator'] ?? null,
            TargetOperator::class,
            TargetOperator::GreaterThanOrEqual->value,
        );

        $data['scope_type'] = $this->normalizeNullableEnumValue(
            $data['scope_type'] ?? $data['standard_scope_type'] ?? $data['standard_scope'] ?? null,
            UnitType::class,
        );

        $data['standard_version'] = filled($data['standard_version'] ?? null) ? (int) $data['standard_version'] : 1;
        $data['target_value'] = filled($data['target_value'] ?? null) ? (float) $data['target_value'] : null;
        $data['weight'] = filled($data['weight'] ?? null) ? (int) $data['weight'] : 1;
        $data['evidence_required'] = $this->normalizeBoolean($data['evidence_required'] ?? null, true);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validatedData(array $data): array
    {
        $validated = Validator::make($data, [
            'standard_code' => ['required', 'string', 'max:255'],
            'standard_name' => ['required', 'string', 'max:255'],
            'standard_category_code' => ['required', 'string', 'max:255'],
            'standard_category_name' => ['nullable', 'string', 'max:255'],
            'standard_subcategory_code' => ['nullable', 'string', 'max:255'],
            'standard_subcategory_name' => ['nullable', 'string', 'max:255'],
            'scope_type' => ['nullable', Rule::in(Arr::pluck(UnitType::cases(), 'value'))],
            'spmi_period_name' => ['nullable', 'string', 'max:255'],
            'standard_statement_code' => ['nullable', 'string', 'max:255'],
            'standard_statement' => ['nullable', 'string'],
            'standard_description' => ['nullable', 'string'],
            'standard_status' => ['required', Rule::in(Arr::pluck(QualityStandardStatus::cases(), 'value'))],
            'standard_version' => ['required', 'integer', 'min:1'],
            'indicator_code' => ['required', 'string', 'max:255'],
            'indicator_statement' => ['required', 'string'],
            'indicator_type' => ['required', Rule::in(Arr::pluck(StandardIndicatorType::cases(), 'value'))],
            'target_operator' => ['required', Rule::in(Arr::pluck(TargetOperator::cases(), 'value'))],
            'target_value' => ['nullable', 'numeric'],
            'target_unit' => ['nullable', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1'],
            'evidence_required' => ['required', 'boolean'],
            'evidence_description' => ['nullable', 'string'],
        ])->validate();

        return array_replace([
            'standard_category_name' => null,
            'standard_subcategory_code' => null,
            'standard_subcategory_name' => null,
            'scope_type' => null,
            'spmi_period_name' => null,
            'standard_statement_code' => 'PS-001',
            'standard_statement' => null,
            'standard_description' => null,
            'target_value' => null,
            'target_unit' => null,
            'evidence_description' => null,
        ], $validated);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveStandardCategory(array $data): StandardCategory
    {
        $category = StandardCategory::query()->firstOrCreate(
            ['code' => $data['standard_category_code']],
            [
                'name' => filled($data['standard_category_name'] ?? null)
                    ? $data['standard_category_name']
                    : $data['standard_category_code'],
            ],
        );

        if (filled($data['standard_category_name'] ?? null) && $category->name !== $data['standard_category_name']) {
            $category->update(['name' => $data['standard_category_name']]);
        }

        if (blank($data['standard_subcategory_code'] ?? null)) {
            return $category;
        }

        $subcategory = StandardCategory::query()->firstOrCreate(
            ['code' => $data['standard_subcategory_code']],
            [
                'parent_id' => $category->getKey(),
                'name' => filled($data['standard_subcategory_name'] ?? null)
                    ? $data['standard_subcategory_name']
                    : $data['standard_subcategory_code'],
            ],
        );

        $updates = [];

        if ((int) $subcategory->parent_id !== (int) $category->getKey()) {
            $updates['parent_id'] = $category->getKey();
        }

        if (filled($data['standard_subcategory_name'] ?? null) && $subcategory->name !== $data['standard_subcategory_name']) {
            $updates['name'] = $data['standard_subcategory_name'];
        }

        if ($updates !== []) {
            $subcategory->update($updates);
        }

        return $subcategory;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveStandardStatement(QualityStandard $standard, array $data): StandardStatement
    {
        $statementCode = filled($data['standard_statement_code'] ?? null)
            ? $data['standard_statement_code']
            : 'PS-001';

        return StandardStatement::query()->updateOrCreate(
            [
                'quality_standard_id' => $standard->getKey(),
                'code' => $statementCode,
            ],
            [
                'statement' => $data['standard_statement'] ?: ($standard->description ?: $standard->name),
                'sort_order' => $this->statementSortOrder($statementCode),
            ],
        );
    }

    private function statementSortOrder(string $statementCode): int
    {
        if (preg_match('/(\d+)$/', $statementCode, $matches) === 1) {
            return max(1, (int) $matches[1]);
        }

        return 1;
    }

    /**
     * @param  class-string<\BackedEnum&HasLabel>  $enum
     */
    private function normalizeNullableEnumValue(mixed $state, string $enum): ?string
    {
        if (blank($state)) {
            return null;
        }

        return $this->normalizeEnumValue($state, $enum, '');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSpmiPeriodId(array $data): ?int
    {
        if (blank($data['spmi_period_name'] ?? null)) {
            return null;
        }

        $period = SpmiPeriod::query()
            ->where('name', $data['spmi_period_name'])
            ->first();

        if ($period === null) {
            Validator::make([], [])->after(function ($validator) use ($data): void {
                $validator->errors()->add('spmi_period_name', "Periode SPMI [{$data['spmi_period_name']}] tidak ditemukan.");
            })->validate();
        }

        return $period?->getKey();
    }

    private function authorizeStandardImport(QualityStandard $standard): void
    {
        if ($standard->exists) {
            $this->user->can('update', $standard) || abort(403);

            return;
        }

        $this->user->can('create', QualityStandard::class) || abort(403);
    }

    private function authorizeIndicatorImport(StandardIndicator $indicator): void
    {
        if ($indicator->exists) {
            $this->user->can('update', $indicator) || abort(403);

            return;
        }

        $this->user->can('create', StandardIndicator::class) || abort(403);
    }

    /**
     * @param  class-string<\BackedEnum&HasLabel>  $enum
     */
    private function normalizeEnumValue(mixed $state, string $enum, string $default): string
    {
        if (blank($state)) {
            return $default;
        }

        $normalizedState = Str::lower(trim((string) $state));

        foreach ($enum::cases() as $case) {
            if ($normalizedState === Str::lower($case->value)) {
                return $case->value;
            }

            if ($normalizedState === Str::lower((string) $case->getLabel())) {
                return $case->value;
            }
        }

        return (string) $state;
    }

    private function normalizeBoolean(mixed $state, bool $default): bool
    {
        if (blank($state)) {
            return $default;
        }

        return match (Str::lower(trim((string) $state))) {
            '1', 'true', 'yes', 'y', 'on', 'ya' => true,
            '0', 'false', 'no', 'n', 'off', 'tidak' => false,
            default => (bool) $state,
        };
    }
}
