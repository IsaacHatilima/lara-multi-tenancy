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

/** PlanFeatureResource */
export type PlanFeature = {
    key: string;
    unitLabel: string | null;
    included: boolean;
    limit: number | null;
};

/** PlanDetailsResource */
export type PlanDetails = {
    id: string;
    code: string;
    name: string;
    description: string | null;
    sortOrder: number;
    status: Plan['status'];
    statusLabel: string;
    tenantName: string | null;
    features: PlanFeature[];
};

/** BillingInterval::options() */
export type IntervalOption = {
    value: 'month' | 'year';
    label: string;
};
