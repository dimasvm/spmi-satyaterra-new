<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'description',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function qualityStandards(): HasMany
    {
        return $this->hasMany(QualityStandard::class);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeLeaf(Builder $query): Builder
    {
        return $query->whereDoesntHave('children');
    }

    public function getQualifiedNameAttribute(): string
    {
        if ($this->relationLoaded('parent') && $this->parent !== null) {
            return "{$this->parent->name} / {$this->name}";
        }

        return $this->name;
    }

    public function isInUse(): bool
    {
        return $this->qualityStandards()->exists() || $this->children()->exists();
    }

    /**
     * @return array<int, string>
     */
    public static function optionsForStandards(?int $selectedId = null): array
    {
        return self::query()
            ->with('parent')
            ->leaf()
            ->when(
                $selectedId !== null,
                fn (Builder $query): Builder => $query->orWhere($query->getModel()->getQualifiedKeyName(), $selectedId),
            )
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (self $category): array => [$category->id => $category->qualified_name])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function parentOptions(?int $excludedId = null): array
    {
        return self::query()
            ->topLevel()
            ->when($excludedId !== null, fn (Builder $query): Builder => $query->whereKeyNot($excludedId))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function hierarchicalOptions(): array
    {
        return self::query()
            ->with('parent')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (self $category): array => [$category->id => $category->qualified_name])
            ->all();
    }
}
