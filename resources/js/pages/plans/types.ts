export type Currency = 'EUR' | 'USD' | 'GBP';

export type BillingInterval = 'month' | 'year';

/** PlanResource */
export type Plan = {
    id: string;
    name: string;
    code: string;
    description: string | null;
    priceMinor: number;
    /** Formatted with its currency, such as "€49.00". */
    price: string;
    currency: Currency;
    interval: BillingInterval;
    intervalLabel: string;
    trialDays: number;
};

/** Currency::options() */
export type CurrencyOption = {
    value: Currency;
    label: string;
};

/** BillingInterval::options() */
export type BillingIntervalOption = {
    value: BillingInterval;
    label: string;
};

/** PlanFeatureResource: a feature the plan includes, with its limit there. */
export type PlanFeature = {
    id: number;
    key: string;
    name: string;
    limitUnitLabel: string | null;
    /** Null means unlimited. */
    limit: number | null;
};
