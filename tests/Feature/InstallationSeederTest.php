<?php

namespace Tests\Feature;

use App\Models\AmiAudit;
use App\Models\AmiChecklist;
use App\Models\AmiFinding;
use App\Models\CorrectiveAction;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityDocument;
use App\Models\QualityStandard;
use App\Models\StandardIndicator;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstallationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_seeder_prepares_demo_spmi_workflow_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(6, Role::query()->count());
        $this->assertSame(10, Unit::query()->count());
        $this->assertSame(22, User::query()->count());

        $this->assertSame(1, User::role('super_admin')->count());
        $this->assertSame(3, User::role('admin_lpm')->count());
        $this->assertSame(2, User::role('pimpinan')->count());
        $this->assertSame(5, User::role('auditor')->count());
        $this->assertSame(10, User::role('unit_pic')->count());
        $this->assertSame(1, User::role('viewer')->count());

        $this->assertSame(10, QualityStandard::query()->count());
        $this->assertSame(200, StandardIndicator::query()->count());
        $this->assertSame(40, QualityDocument::query()->count());
        $this->assertTrue(
            QualityStandard::query()
                ->whereDoesntHave('indicators')
                ->doesntExist(),
        );
        $this->assertTrue(
            QualityStandard::query()
                ->get()
                ->every(fn (QualityStandard $standard): bool => $standard->indicators()->count() === 20
                    && $standard->documents()->count() === 4),
        );

        $this->assertSame(500, IndicatorUnitAssignment::query()->count());
        $this->assertSame(300, IndicatorAchievement::query()->count());

        $this->assertSame(5, AmiAudit::query()->count());
        $this->assertSame(60, AmiChecklist::query()->count());
        $this->assertSame(40, AmiFinding::query()->count());
        $this->assertSame(40, CorrectiveAction::query()->count());

        $this->assertDatabaseHas('corrective_actions', ['status' => 'draft']);
        $this->assertDatabaseHas('corrective_actions', ['status' => 'submitted']);
        $this->assertDatabaseHas('corrective_actions', ['status' => 'need_revision']);
        $this->assertDatabaseHas('corrective_actions', ['status' => 'accepted']);
    }
}
