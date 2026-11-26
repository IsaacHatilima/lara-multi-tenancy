<?php

use App\Actions\Features\IsFeatureGatedAction;
use App\Actions\Tenants\IsTenantManagementAvailableAction;

test('features are gated only on a tenant host of a managed deployment', function (bool $managed, string $host, bool $expected) {
    $action = new IsFeatureGatedAction($managed, new IsTenantManagementAvailableAction($managed, ['mitarbo.test']));

    expect($action->handle($host))->toBe($expected);
})->with([
    'managed, central host' => [true, 'mitarbo.test', false],
    'managed, central host in upper case' => [true, 'Mitarbo.TEST', false],
    'managed, tenant subdomain' => [true, 'acme.mitarbo.test', true],
    'managed, custom tenant domain' => [true, 'portal.acme.example', true],
    'self-hosted, central host' => [false, 'mitarbo.test', false],
    'self-hosted, tenant domain' => [false, 'portal.acme.example', false],
]);
