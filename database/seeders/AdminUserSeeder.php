<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 初期管理ユーザー（ADM-001/002）。
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@locona.test'],
            [
                'name' => 'LOCONA 管理者',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator@locona.test'],
            [
                'name' => 'LOCONA 運用担当',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OPERATOR,
                'is_active' => true,
            ]
        );
    }
}
