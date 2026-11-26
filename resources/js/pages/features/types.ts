/** FeaturePlanResource */
export type FeaturePlan = {
    id: string;
    code: string;
    name: string;
    tenantName: string | null;
};

/** FeatureResource */
export type Feature = {
    key: string;
    unitLabel: string | null;
    plans: FeaturePlan[];
};
