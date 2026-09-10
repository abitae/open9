<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@open9.dev'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'status' => RecordStatus::Active->value,
                'email_verified_at' => now(),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $admin->assignRole('super-admin');
        }
    }
}
