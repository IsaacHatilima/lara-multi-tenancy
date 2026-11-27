<?php

namespace Database\Seeders;

use App\Actions\Domains\CreateDomainAction;
use App\Data\Domains\SaveDomainData;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;
use Stancl\Tenancy\Database\TenantDatabaseManagers\PostgreSQLSchemaManager;

/**
 * Seeds two demo companies, each with a subdomain of the central host and one login inside its own database.
 */
class TenantSeeder extends Seeder
{
    public const string PASSWORD = CentralUserSeeder::PASSWORD;

    /**
     * Each handle names the company's subdomain and login email; it is not the tenant id.
     *
     * @var list<string>
     */
    public const array HANDLES = ['tenant1', 'tenant2'];

    public function __construct(
        private readonly CreateDomainAction $createDomain,
    ) {
    }

    public function run(): void
    {
        foreach (self::HANDLES as $handle) {
            $this->seedTenant($handle);
        }
    }

    public static function hostname(string $handle): string
    {
        return $handle.'.'.parse_url(config('app.url'), PHP_URL_HOST);
    }

    public static function email(string $handle): string
    {
        return $handle.'@mail.test';
    }

    /**
     * A name-based (v5) UUID, so reseeding gives the company the same id, schema and storage prefix.
     */
    public static function tenantId(string $handle): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_DNS, self::hostname($handle))->toString();
    }

    protected function seedTenant(string $handle): void
    {
        $tenantId = self::tenantId($handle);

        if (Tenant::query()->whereKey($tenantId)->exists()) {
            return;
        }

        $this->dropLeftoverDatabase($tenantId);

        $tenant = Tenant::factory()->withCompanyDetails()->create([
            'id' => $tenantId,
            'email' => self::email($handle),
        ]);

        $this->createDomain->handle($tenant, new SaveDomainData(self::hostname($handle), null));

        $tenant->run(fn () => User::factory()->named($tenant->name, 'Admin')->create([
            'email' => self::email($handle),
            'password' => self::PASSWORD,
        ]));
    }

    /**
     * migrate:fresh and migrate:refresh wipe the central tables but leave tenant databases behind, which would block
     * recreating the tenant. The tenant's database role was recorded on the wiped tenant row, so for Postgres schemas
     * only the schema is dropped.
     */
    protected function dropLeftoverDatabase(string $tenantId): void
    {
        $tenant = new Tenant();
        $tenant->id = $tenantId;

        $database = $tenant->database();
        $manager = $database->manager();
        $name = $database->getName();

        if (! $manager->databaseExists($name)) {
            return;
        }

        if ($manager instanceof PostgreSQLSchemaManager) {
            $manager->connection()->statement(sprintf('DROP SCHEMA "%s" CASCADE', $name));

            return;
        }

        $manager->deleteDatabase($tenant);
    }
}
