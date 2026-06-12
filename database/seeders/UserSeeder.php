<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const PASSWORD = '123456';

    /**
     * Seed demo users for every SPMI role.
     */
    public function run(): void
    {
        $units = Unit::query()->orderBy('code')->get()->keyBy('code');
        $lpmUnitId = $units->get('LPM')?->id;

        $this->createUser('superadmin@spmi.test', $lpmUnitId, 'Super Admin SPMI', 'super_admin');

        foreach (range(1, 3) as $number) {
            $this->createUser(
                email: "admin.lpm{$number}@spmi.test",
                unitId: $lpmUnitId,
                name: "Admin LPM {$number}",
                role: 'admin_lpm',
            );
        }

        foreach (range(1, 2) as $number) {
            $this->createUser(
                email: "pimpinan{$number}@spmi.test",
                unitId: null,
                name: "Pimpinan {$number}",
                role: 'pimpinan',
            );
        }

        foreach (range(1, 5) as $number) {
            $this->createUser(
                email: "auditor{$number}@spmi.test",
                unitId: $lpmUnitId,
                name: "Auditor AMI {$number}",
                role: 'auditor',
            );
        }

        foreach ($units as $unit) {
            $this->createUser(
                email: 'pic.'.strtolower($unit->code).'@spmi.test',
                unitId: $unit->id,
                name: 'PIC '.$unit->name,
                role: 'unit_pic',
            );
        }

        $this->createUser('viewer@spmi.test', null, 'Viewer SPMI', 'viewer');
    }

    private function createUser(string $email, ?int $unitId, string $name, string $role): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'unit_id' => $unitId,
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles([$role]);

        return $user;
    }
}
