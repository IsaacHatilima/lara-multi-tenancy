<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform's own login on the central database.
 */
class CentralUserSeeder extends Seeder
{
    public const string EMAIL = 'central@mail.test';

    public const string PASSWORD = 'Password1#';

    public function run(): void
    {
        if (User::query()->where('email', self::EMAIL)->exists()) {
            return;
        }

        User::factory()->create([
            'name' => 'Central Admin',
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);
    }
}
