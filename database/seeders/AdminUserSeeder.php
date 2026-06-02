<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the initial administrator user.
     */
    public function run(): void
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
}
