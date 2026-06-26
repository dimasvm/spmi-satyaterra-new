<?php

namespace Database\Factories;

use App\Models\CampusProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampusProfile>
 */
class CampusProfileFactory extends Factory
{
    protected $model = CampusProfile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pddikti_id' => fake()->uuid(),
            'name' => fake()->company().' '.fake()->randomElement(['University', 'Institut', 'Sekolah Tinggi']),
            'short_name' => strtoupper(fake()->lexify('???')),
            'npsn' => fake()->numerify('##########'),
            'accreditation' => fake()->randomElement(['Unggul', 'Baik Sekali', 'Baik', 'A', 'B', 'C']),
            'status' => 'Aktif',
            'type' => fake()->randomElement(['Universitas', 'Institut', 'Sekolah Tinggi', 'Politeknik']),
            'address' => fake()->address(),
            'province' => fake()->state(),
            'city' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'logo_url' => null,
            'total_students' => fake()->numberBetween(500, 30000),
            'total_lecturers' => fake()->numberBetween(50, 500),
            'total_study_programs' => fake()->numberBetween(5, 40),
            'faculties' => [],
            'student_stats' => [],
            'accreditation_stats' => [],
            'raw_data' => null,
            'is_active' => false,
            'last_synced_at' => now(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
