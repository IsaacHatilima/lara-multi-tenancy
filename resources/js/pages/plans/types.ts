/** PlanResource */
export type Plan = {
    id: string;
    name: string;
    code: string;
    description: string | null;
};

/** PlanFeatureResource */
export type PlanFeature = {
    id: number;
    key: string;
    name: string;
    limitUnitLabel: string | null;
    included: boolean;
    limit: number | null;
};
