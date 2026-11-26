export type LimitUnit = 'megabytes' | 'count';

/** FeatureResource */
export type Feature = {
    id: number;
    key: string;
    name: string;
    description: string | null;
    limitUnit: LimitUnit | null;
    limitUnitLabel: string | null;
};

/** LimitUnit::options() */
export type LimitUnitOption = {
    value: LimitUnit;
    label: string;
};
