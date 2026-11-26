<?php

use App\Actions\Tenants\IsTenantManagementAvailableAction;

test('tenant management is available only on a central host of a managed deployment', function (bool $managed, string $host, bool $expected) {
    $action = new IsTenantManagementAvailableAction($managed, ['mitarbo.test']);

    expect($action->handle($host))->toBe($expected);
})->with([
    'managed, central host' => [true, 'mitarbo.test', true],
    'managed, central host in upper case' => [true, 'Mitarbo.TEST', true],
    'managed, tenant subdomain' => [true, 'acme.mitarbo.test', false],
    'managed, custom tenant domain' => [true, 'portal.acme.example', false],
    'self-hosted, central host' => [false, 'mitarbo.test', false],
    'self-hosted, tenant domain' => [false, 'portal.acme.example', false],
]);
