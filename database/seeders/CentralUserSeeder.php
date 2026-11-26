<?php

namespace Database\Seeders;

use App\Enums\CentralPermission;
use App\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform's own login on the central database. On a managed deployment it also holds the central staff
 * permissions, which PermissionSeeder has created by the time this runs.
 */
class CentralUserSeeder extends Seeder
{
    public const string EMAIL = 'central@mail.test';

    public const string PASSWORD = 'Password1#';

    public function __construct(
        #[Config('mitarbo.managed_deployment')]
        private readonly bool $managedDeployment,
    ) {
    }

    public function run(): void
    {
        $user = User::query()->where('email', self::EMAIL)->first() ?? User::factory()->create([
            'name' => 'Central Admin',
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        if ($this->managedDeployment) {
            $user->givePermissionTo(CentralPermission::cases());
        }
    }
}
