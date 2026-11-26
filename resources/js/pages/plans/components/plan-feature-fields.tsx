import { PuzzleIcon } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PlanFeature } from '../types';

type Props = {
    features: PlanFeature[];
    errors: Partial<Record<string, string>>;
};

/** Every feature row, each included or not; an included feature sends its key and limit, so one left unchecked is removed. */
export function PlanFeatureFields({ features, errors }: Props) {
    if (features.length === 0) {
        return (
            <Empty className="border">
                <EmptyHeader>
                    <EmptyMedia variant="icon">
                        <PuzzleIcon />
                    </EmptyMedia>
                    <EmptyTitle>No features yet</EmptyTitle>
                    <EmptyDescription>
                        Features come from the code. Each one appears here once
                        it is built.
                    </EmptyDescription>
                </EmptyHeader>
            </Empty>
        );
    }

    return (
        <ul className="flex flex-col divide-y">
            {features.map((feature, index) => (
                <PlanFeatureField
                    key={feature.key}
                    feature={feature}
                    index={index}
                    errors={errors}
                />
            ))}
        </ul>
    );
}

function PlanFeatureField({
    feature,
    index,
    errors,
}: {
    feature: PlanFeature;
    index: number;
    errors: Props['errors'];
}) {
    const [included, setIncluded] = useState(feature.included);
    const id = `feature-${feature.key}`;

    return (
        <li className="flex flex-col gap-2 py-2.5 first:pt-0 last:pb-0">
            <div className="flex min-h-(--control-height) items-center gap-3">
                <Checkbox
                    id={id}
                    checked={included}
                    onCheckedChange={setIncluded}
                />
                <Label htmlFor={id} className="min-w-0 flex-1 font-mono">
                    <span className="truncate">{feature.key}</span>
                </Label>
                {included && (
                    <>
                        <input
                            type="hidden"
                            name={`features[${index}][key]`}
                            value={feature.key}
                        />
                        <Input
                            name={`features[${index}][limit]`}
                            type="number"
                            min={0}
                            step={1}
                            defaultValue={feature.limit ?? ''}
                            placeholder="Unlimited"
                            aria-label={`Limit for ${feature.key}`}
                            className="w-32 text-right tabular-nums"
                        />
                        {feature.unitLabel && (
                            <span className="text-muted-foreground">
                                {feature.unitLabel}
                            </span>
                        )}
                    </>
                )}
            </div>
            <InputError message={errors[`features.${index}.key`]} />
            <InputError message={errors[`features.${index}.limit`]} />
        </li>
    );
}
