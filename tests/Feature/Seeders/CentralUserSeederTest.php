<?php

use App\Enums\CentralPermission;
use App\Models\User;
use Database\Seeders\CentralUserSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    config(['mitarbo.managed_deployment' => true]);
});

test('a managed deployment seeds the central user holding plans.manage', function () {
    $this->seed([PermissionSeeder::class, CentralUserSeeder::class]);

    expect(User::query()->where('email', CentralUserSeeder::EMAIL)->sole()->checkPermissionTo(CentralPermission::ManagePlans))->toBeTrue();
});

test('an existing central user is kept and given plans.manage', function () {
    $user = User::factory()->named('Existing', 'Admin')->create(['email' => CentralUserSeeder::EMAIL]);

    $this->seed([PermissionSeeder::class, CentralUserSeeder::class]);

    expect(User::query()->sole())
        ->id->toBe($user->id)
        ->profile->first_name->toBe('Existing')
        ->and($user->fresh()->checkPermissionTo(CentralPermission::ManagePlans))->toBeTrue();
});

test('reseeding leaves one user, one permission and one grant', function () {
    $this->seed([PermissionSeeder::class, CentralUserSeeder::class]);
    $this->seed([PermissionSeeder::class, CentralUserSeeder::class]);

    expect(User::query()->count())->toBe(1)
        ->and(Permission::query()->count())->toBe(1)
        ->and(DB::table('model_has_permissions')->count())->toBe(1);
});

test('a self-hosted install seeds the central user without permissions', function () {
    config(['mitarbo.managed_deployment' => false]);

    $this->seed([PermissionSeeder::class, CentralUserSeeder::class]);

    expect(User::query()->where('email', CentralUserSeeder::EMAIL)->exists())->toBeTrue()
        ->and(DB::table('model_has_permissions')->count())->toBe(0);
});
