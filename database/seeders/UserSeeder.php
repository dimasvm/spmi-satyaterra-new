<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the initial administrator user.
     */
    public function run(): void
    {
        $this->createSuperAdmin();

        $this->createAdminLpm();

        $this->createUnitPic();
    }

    private function createSuperAdmin()
    {
        $lpm = Unit::where('code', 'LPM')->first();

        $admin = User::updateOrCreate(
            ['email' => 'dimasvm@gmail.com'],
            [
                'unit_id' => $lpm?->id,
                'name' => 'Dimas Maulana',
                'password' => Hash::make('123456'),
                'is_active' => true,
            ],
        );

        $admin->forceFill([
            'email_verified_at' => now(),
        ])->save();

        $admin->syncRoles(['super_admin']);
    }

    private function createAdminLpm()
    {
        $user1 = $this->createUser(
            'shaleh@satyaterrabhinneka.com',
            Unit::where('code', 'LPM')->first()?->id,
            'Shaleh',
            Hash::make(123456),
            true,
            now()
        );

        $user1->syncRoles(['admin_lpm']);
    }

    private function createUnitPic()
    {
        $user1 = $this->createUser(
            'teknikinformatika@satyaterrabhinneka.com',
            Unit::where('code', 'TI')->first()?->id,
            'Teguh',
            Hash::make(123456),
            true,
            now()
        );

        $user1->syncRoles(['unit_pic']);
    }

    private function createUser(
        string $email,
        ?int $unit_id,
        string $name,
        string $password,
        bool $is_active = true,
        ?Carbon $email_verified_at = null
    )
    {
        return User::create([
            'email' => $email,
            'unit_id' => $unit_id,
            'name' => $name,
            'password' => $password,
            'is_active' => $is_active,
            'email_verified_at' => $email_verified_at ?? now()
        ]);
    }
}
