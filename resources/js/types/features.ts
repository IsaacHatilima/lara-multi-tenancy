export type FeatureItem = {
    enabled: boolean;
    limit: number | null;
    unit: 'megabytes' | 'count' | null;
    availableOn: string[];
};

export type SubscriptionStatus =
    | 'trialing'
    | 'active'
    | 'past_due'
    | 'read_only'
    | 'canceled'
    | 'ended';

export type FeatureSet = {
    version: string;
    gated: boolean;
    plan: { name: string; code: string } | null;
    subscription: {
        status: SubscriptionStatus;
        trialEndsAt: string | null;
        graceEndsAt: string | null;
    } | null;
    readOnly: boolean;
    items: Record<string, FeatureItem>;
};
