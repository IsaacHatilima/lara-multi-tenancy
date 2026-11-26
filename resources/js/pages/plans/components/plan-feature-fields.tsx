import { Link } from '@inertiajs/react';
import { PuzzleIcon } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Empty,
    EmptyContent,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { create } from '@/routes/features';
import type { PlanFeature } from '../types';

type Props = {
    features: PlanFeature[];
    errors: Partial<Record<string, string>>;
};

/** Every feature, each included or not. A checked feature submits its id and limit; one left unchecked is removed. */
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
                        Add a feature first, then include it here.
                    </EmptyDescription>
                </EmptyHeader>
                <EmptyContent>
                    <Button
                        nativeButton={false}
                        variant="outline"
                        render={<Link href={create()} />}
                    >
                        New feature
                    </Button>
                </EmptyContent>
            </Empty>
        );
    }

    return (
        <ul className="flex flex-col divide-y">
            {features.map((feature, index) => (
                <PlanFeatureField
                    key={feature.id}
                    feature={feature}
                    index={index}
                    errors={errors}
                />
            ))}
        </ul>
    );
}

type FieldProps = {
    feature: PlanFeature;
    index: number;
    errors: Props['errors'];
};

function PlanFeatureField({ feature, index, errors }: FieldProps) {
    const [included, setIncluded] = useState(feature.included);
    const id = `feature-${feature.id}`;

    return (
        <li className="flex flex-col gap-2 py-2.5 first:pt-0 last:pb-0">
            <div className="flex min-h-(--control-height) items-center gap-3">
                <Checkbox
                    id={id}
                    name={`features[${index}][id]`}
                    value={String(feature.id)}
                    checked={included}
                    onCheckedChange={setIncluded}
                />
                <Label
                    htmlFor={id}
                    className="flex min-w-0 flex-1 flex-col items-start gap-0"
                >
                    <span className="truncate">{feature.name}</span>
                    <span className="truncate font-mono text-xs text-muted-foreground">
                        {feature.key}
                    </span>
                </Label>
                {included && (
                    <>
                        <Input
                            name={`features[${index}][limit]`}
                            type="number"
                            min={0}
                            step={1}
                            defaultValue={feature.limit ?? ''}
                            placeholder="Unlimited"
                            aria-label={`Limit for ${feature.name}`}
                            className="w-32 text-right tabular-nums"
                        />
                        {feature.limitUnitLabel && (
                            <span className="text-muted-foreground">
                                {feature.limitUnitLabel}
                            </span>
                        )}
                    </>
                )}
            </div>
            <InputError message={errors[`features.${index}.id`]} />
            <InputError message={errors[`features.${index}.limit`]} />
        </li>
    );
}
