<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampusProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'pddikti_id',
        'name',
        'short_name',
        'npsn',
        'accreditation',
        'status',
        'type',
        'address',
        'province',
        'city',
        'phone',
        'email',
        'website',
        'logo_url',
        'total_students',
        'total_lecturers',
        'total_study_programs',
        'faculties',
        'student_stats',
        'accreditation_stats',
        'raw_data',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'faculties' => 'array',
        'student_stats' => 'array',
        'accreditation_stats' => 'array',
        'raw_data' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
        'total_students' => 'integer',
        'total_lecturers' => 'integer',
        'total_study_programs' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Activate this campus and deactivate all others.
     */
    public function activate(): void
    {
        self::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Get the accreditation badge color class.
     */
    public function getAccreditationColorAttribute(): string
    {
        return match (strtolower($this->accreditation ?? '')) {
            'unggul', 'a' => 'emerald',
            'baik sekali', 'b' => 'blue',
            'baik', 'c' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get the active campus profile or null.
     */
    public static function getActive(): ?self
    {
        return self::active()->first();
    }
}
