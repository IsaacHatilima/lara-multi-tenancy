/** DomainResource */
export type Domain = {
    id: number;
    domain: string;
    slot: 'primary' | 'secondary';
    slotLabel: string;
};

/** TenantResource */
export type Tenant = {
    id: string;
    name: string;
    legalName: string | null;
    email: string | null;
    phone: string | null;
    website: string | null;
    registrationNumber: string | null;
    taxNumber: string | null;
    addressLine1: string | null;
    addressLine2: string | null;
    city: string | null;
    state: string | null;
    postalCode: string | null;
    countryCode: string | null;
    timezone: string | null;
    locale: string | null;
    domains: Domain[];
    createdAt: string;
};

/** A Laravel LengthAwarePaginator as Inertia serialises it. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};

/** A company can have one domain in each slot. */
export const MAX_DOMAINS = 2;

/** SubscriptionResource */
export type Subscription = {
    planName: string;
    status:
        | 'trialing'
        | 'active'
        | 'past_due'
        | 'read_only'
        | 'canceled'
        | 'ended';
    statusLabel: string;
    price: string;
    startedAt: string;
    currentPeriodEndsAt: string;
    paymentMethod: string | null;
};

/** PaymentResource */
export type Payment = {
    reference: string;
    description: string;
    status: 'paid' | 'pending' | 'failed' | 'refunded';
    statusLabel: string;
    amount: string;
    attemptedAt: string;
};

/** TenantComplianceResource */
export type Compliance = {
    legalName: string | null;
    registrationNumber: string | null;
    taxNumber: string | null;
    countryCode: string | null;
    address: string[];
    termsVersion: string;
    termsAcceptedAt: string | null;
    dataProcessingAgreementSignedAt: string | null;
    dataRegion: string;
};

/** TenantOverviewResource */
export type TenantOverview = {
    tenant: Tenant;
    subscription: Subscription | null;
    payments: Payment[];
    totalPaid: string;
    compliance: Compliance;
};
