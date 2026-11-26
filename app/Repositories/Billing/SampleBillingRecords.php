<?php

declare(strict_types=1);

namespace App\Repositories\Billing;

use App\Data\Billing\PaymentRecord;
use App\Data\Billing\SubscriptionRecord;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use Carbon\CarbonImmutable;

/**
 * Stand-in billing data until a payment provider is connected and subscriptions and payments get their own tables.
 * Values are derived from the tenant id, so each company always shows the same plan, status and history;
 * about one in seven companies has no paid subscription and no payments.
 * Delete this class when the billing repositories read real records.
 */
final class SampleBillingRecords
{
    public const string CURRENCY = 'EUR';

    private const int MONTHS_BILLED = 8;

    private const array PLANS = [
        ['name' => 'Basic', 'amountMinor' => 1900],
        ['name' => 'Pro', 'amountMinor' => 4900],
    ];

    private const array CARD_BRANDS = ['Visa', 'Mastercard', 'American Express'];

    public static function subscription(Tenant $tenant): ?SubscriptionRecord
    {
        $seed = self::seed($tenant);

        if (! self::isSubscribed($seed)) {
            return null;
        }

        $plan = self::PLANS[$seed % count(self::PLANS)];
        $lastBilledAt = self::lastBilledAt($seed);

        return new SubscriptionRecord(
            planName: $plan['name'],
            status: self::isPastDue($seed) ? SubscriptionStatus::PastDue : SubscriptionStatus::Active,
            amountMinor: $plan['amountMinor'],
            currency: self::CURRENCY,
            startedAt: $lastBilledAt->subMonths(self::MONTHS_BILLED - 1),
            currentPeriodEndsAt: $lastBilledAt->addMonth(),
            cardBrand: self::CARD_BRANDS[$seed % count(self::CARD_BRANDS)],
            cardLastFour: str_pad((string) ($seed % 10000), 4, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Newest first.
     *
     * @return list<PaymentRecord>
     */
    public static function payments(Tenant $tenant): array
    {
        $seed = self::seed($tenant);
        $subscription = self::subscription($tenant);

        if ($subscription === null) {
            return [];
        }

        $lastBilledAt = self::lastBilledAt($seed);
        $invoicePrefix = 'INV-'.strtoupper(substr(md5((string) $tenant->getTenantKey()), 0, 6));

        return array_map(
            fn (int $monthsAgo): PaymentRecord => new PaymentRecord(
                reference: $invoicePrefix.'-'.str_pad((string) (self::MONTHS_BILLED - $monthsAgo), 4, '0', STR_PAD_LEFT),
                description: __(':plan plan, monthly', ['plan' => $subscription->planName]),
                status: $monthsAgo === 0 && self::isPastDue($seed) ? PaymentStatus::Failed : PaymentStatus::Paid,
                amountMinor: $subscription->amountMinor,
                currency: self::CURRENCY,
                attemptedAt: $lastBilledAt->subMonths($monthsAgo),
            ),
            range(0, self::MONTHS_BILLED - 1),
        );
    }

    private static function seed(Tenant $tenant): int
    {
        return crc32((string) $tenant->getTenantKey());
    }

    private static function isSubscribed(int $seed): bool
    {
        return $seed % 7 !== 0;
    }

    private static function isPastDue(int $seed): bool
    {
        return $seed % 3 === 1;
    }

    private static function lastBilledAt(int $seed): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfDay()->subDays($seed % 28);
    }
}
