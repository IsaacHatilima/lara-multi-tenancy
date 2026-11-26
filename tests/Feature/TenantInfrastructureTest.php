<?php

use App\Models\Tenant;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\RootUrlBootstrapper;

afterEach(function () {
    tenancy()->end();

    if (isset($this->temporaryStoragePath)) {
        File::deleteDirectory($this->temporaryStoragePath);
    }
});

test('tenant disks isolate S3 keys and private local files and restore central paths', function () {
    $this->temporaryStoragePath = sys_get_temp_dir().'/mitarbo-storage-'.bin2hex(random_bytes(8));
    $this->app->useStoragePath($this->temporaryStoragePath);
    config([
        'tenancy.bootstrappers' => [FilesystemTenancyBootstrapper::class],
        'filesystems.disks.s3.key' => 'test-key',
        'filesystems.disks.s3.secret' => 'test-secret',
        'filesystems.disks.s3.bucket' => 'test-bucket',
        'filesystems.disks.s3.url' => 'https://files.example.test',
    ]);
    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'storage-a']));
    $centralLocalPath = Storage::disk('local')->path('file.txt');

    tenancy()->initialize($tenant);

    expect(Storage::disk('s3')->path('file.txt'))->toBe('tenantstorage-a/file.txt');
    expect(Storage::disk('s3')->url('file.txt'))->toBe('https://files.example.test/tenantstorage-a/file.txt');
    expect(Storage::disk('local')->path('file.txt'))->toBe($this->temporaryStoragePath.'/tenantstorage-a/app/private/file.txt');

    tenancy()->end();

    expect(Storage::disk('s3')->path('file.txt'))->toBe('file.txt');
    expect(Storage::disk('local')->path('file.txt'))->toBe($centralLocalPath);
});

test('cache values stay separate for each tenant and the central application', function () {
    config([
        'tenancy.bootstrappers' => [CacheTenancyBootstrapper::class],
        'tenancy.cache.stores' => ['database'],
        'cache.default' => 'database',
        'cache.stores.database.connection' => 'sqlite',
    ]);
    $first = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'cache-a']));
    $second = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'cache-b']));
    Cache::put('setting', 'central');

    tenancy()->initialize($first);
    expect(Cache::get('setting'))->toBeNull();
    Cache::put('setting', 'first tenant');

    tenancy()->initialize($second);
    expect(Cache::get('setting'))->toBeNull();
    Cache::put('setting', 'second tenant');

    tenancy()->initialize($first);
    expect(Cache::get('setting'))->toBe('first tenant');

    tenancy()->end();
    expect(Cache::get('setting'))->toBe('central');
});

test('centrally stored queued jobs carry tenant context only for tenant dispatches', function () {
    config([
        'tenancy.bootstrappers' => [QueueTenancyBootstrapper::class],
        'queue.connections.database.connection' => 'sqlite',
    ]);
    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'queue-a']));

    tenancy()->initialize($tenant);
    Queue::connection('database')->push(new CallQueuedListener(stdClass::class, 'handle', []));
    tenancy()->end();
    Queue::connection('database')->push(new CallQueuedListener(stdClass::class, 'handle', []));

    $payloads = DB::connection('sqlite')->table('jobs')->orderBy('id')->pluck('payload');

    expect($payloads)->toHaveCount(2);
    expect(json_decode($payloads[0], true))->toHaveKey('tenant_id', 'queue-a');
    expect(json_decode($payloads[1], true))->not->toHaveKey('tenant_id');
});

test('tenant links generated outside HTTP use the tenant custom domain and revert centrally', function () {
    config(['tenancy.bootstrappers' => [RootUrlBootstrapper::class]]);
    $tenant = Tenant::withoutEvents(fn () => Tenant::factory()->create(['id' => 'links-a']));
    $tenant->domains()->create(['domain' => 'support.customer.test']);
    $centralUrl = route('dashboard');

    tenancy()->initialize($tenant);

    expect(route('dashboard'))->toBe('https://support.customer.test/dashboard');

    tenancy()->end();

    expect(route('dashboard'))->toBe($centralUrl);
});
