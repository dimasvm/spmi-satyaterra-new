<?php

namespace Tests\Feature;

use App\Enums\SpmiPeriodStatus;
use App\Enums\SpmiSemester;
use App\Models\CampusProfile;
use App\Models\SpmiPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_with_campus_profile_and_stats(): void
    {
        // Seed an active period
        $period = SpmiPeriod::create([
            'name' => 'Periode SPMI 2025/2026',
            'academic_year' => '2025/2026',
            'semester' => SpmiSemester::Tahunan,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(11),
            'status' => SpmiPeriodStatus::Active,
        ]);

        // Seed a campus profile
        $campus = CampusProfile::create([
            'pddikti_id' => 'test-pt-id',
            'name' => 'Universitas Satyaterra',
            'short_name' => 'US',
            'accreditation' => 'Unggul',
            'status' => 'Aktif',
            'type' => 'Universitas',
            'total_students' => 6540,
            'total_lecturers' => 218,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('campus');
        $response->assertViewHas('stats');
        $response->assertViewHas('isFeederConnected');

        $response->assertSee('Universitas Satyaterra');
        $response->assertSee('Sistem Penjaminan Mutu Internal');
        $response->assertSee('Integrasi Neo Feeder PDDikti');
        $response->assertSee('Penetapan Standar');
        $response->assertSee('Pelaksanaan Standar');
        $response->assertSee('Evaluasi (AMI)');
    }
}
