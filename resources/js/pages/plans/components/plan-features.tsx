import { Form } from '@inertiajs/react';
import { Trash2Icon } from 'lucide-react';
import { useState } from 'react';
import PlanFeatureController from '@/actions/App/Http/Controllers/Plans/PlanFeatureController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Plan, PlanFeature } from '../types';
import { AddPlanFeatureSearch } from './add-plan-feature-search';
import { RemovePlanFeatureDialog } from './remove-plan-feature-dialog';

type Props = {
    plan: Pick<Plan, 'id' | 'name'>;
    features: PlanFeature[];
};

/** The features a plan includes, each with its own limit, and a search to add more. */
export function PlanFeatures({ plan, features }: Props) {
    const [removing, setRemoving] = useState<PlanFeature | null>(null);

    return (
        <div className="flex flex-col gap-4">
            <AddPlanFeatureSearch planId={plan.id} />

            {features.length === 0 ? (
                <p className="text-muted-foreground">
                    The plan includes no features yet. Search above to add one.
                </p>
            ) : (
                <ul
                    aria-label="Included features"
                    className="flex flex-col divide-y"
                >
                    {features.map((feature) => (
                        <PlanFeatureRow
                            key={`${feature.id}-${feature.limit ?? 'unlimited'}`}
                            planId={plan.id}
                            feature={feature}
                            onRemove={() => setRemoving(feature)}
                        />
                    ))}
                </ul>
            )}

            {removing && (
                <RemovePlanFeatureDialog
                    planId={plan.id}
                    planName={plan.name}
                    feature={removing}
                    open
                    onOpenChange={(open) => !open && setRemoving(null)}
                />
            )}
        </div>
    );
}

type RowProps = {
    planId: string;
    feature: PlanFeature;
    onRemove: () => void;
};

/** One included feature: its limit saves on its own, and removing it asks first. */
function PlanFeatureRow({ planId, feature, onRemove }: RowProps) {
    const limitId = `limit-${feature.id}`;

    return (
        <li className="py-2.5 first:pt-0 last:pb-0">
            <Form
                {...PlanFeatureController.update.form([planId, feature.id])}
                options={{ preserveScroll: true }}
                className="flex flex-col gap-2"
            >
                {({ processing, errors, isDirty }) => (
                    <>
                        <div className="flex min-h-(--control-height) flex-wrap items-center gap-3">
                            <label
                                htmlFor={limitId}
                                className="flex min-w-0 flex-1 flex-col"
                            >
                                <span className="truncate">{feature.name}</span>
                                <span className="truncate font-mono text-xs text-muted-foreground">
                                    {feature.key}
                                </span>
                            </label>
                            <div className="flex items-center gap-2">
                                <Input
                                    id={limitId}
                                    name="limit"
                                    type="number"
                                    min={0}
                                    step={1}
                                    defaultValue={feature.limit ?? ''}
                                    placeholder="Unlimited"
                                    aria-label={`Limit for ${feature.name}`}
                                    className="w-28 text-right tabular-nums"
                                />
                                {feature.limitUnitLabel && (
                                    <span className="text-muted-foreground">
                                        {feature.limitUnitLabel}
                                    </span>
                                )}
                                <Button
                                    type="submit"
                                    variant="outline"
                                    size="sm"
                                    disabled={processing || !isDirty}
                                >
                                    Save
                                </Button>
                                <Tooltip>
                                    <TooltipTrigger
                                        render={
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon-sm"
                                                aria-label={`Remove ${feature.name}`}
                                                onClick={onRemove}
                                            />
                                        }
                                    >
                                        <Trash2Icon />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        Remove {feature.name}
                                    </TooltipContent>
                                </Tooltip>
                            </div>
                        </div>
                        <InputError message={errors.limit} />
                    </>
                )}
            </Form>
        </li>
    );
}
