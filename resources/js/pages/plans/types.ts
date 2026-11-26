/** PlanResource */
export type Plan = {
    id: string;
    code: string;
    name: string;
    status: 'active' | 'inactive';
    statusLabel: string;
    price: string;
    intervalLabel: string;
    tenantName: string | null;
};

/** BillingInterval::options() */
export type IntervalOption = {
    value: 'month' | 'year';
    label: string;
};
