import { Form, Head, Link } from '@inertiajs/react';
import PlanController from '@/actions/App/Http/Controllers/Plans/PlanController';
import InputError from '@/components/input-error';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/plans';
import { PlanFeatureFields } from './components/plan-feature-fields';
import { PlanStatusBadge } from './components/plan-status-badge';
import type { PlanDetails } from './types';

type Props = {
    plan: PlanDetails;
};

export default function EditPlan({ plan }: Props) {
    return (
        <>
            <Head title={plan.name} />

            <div className="mx-auto flex w-full max-w-160 flex-col gap-6">
                <header className="flex flex-col gap-1">
                    <div className="flex flex-wrap items-center gap-2">
                        <h1 className="text-xl font-semibold">{plan.name}</h1>
                        <PlanStatusBadge plan={plan} />
                    </div>
                    <p className="font-mono text-xs text-muted-foreground">
                        {plan.code}
                    </p>
                    {plan.tenantName && (
                        <p className="text-muted-foreground">
                            Custom plan for {plan.tenantName}
                        </p>
                    )}
                </header>

                <Form
                    {...PlanController.update.form(plan.id)}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <SettingsSection
                                title="Plan"
                                description="The code, price and billing can't be changed here."
                            >
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        autoComplete="off"
                                        defaultValue={plan.name}
                                    />
                                    <InputError message={errors.name} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="description">
                                        Description
                                    </Label>
                                    <Textarea
                                        id="description"
                                        name="description"
                                        defaultValue={plan.description ?? ''}
                                    />
                                    <InputError message={errors.description} />
                                </div>
                                <div className="grid gap-2 sm:max-w-1/2">
                                    <Label htmlFor="sort_order">
                                        Display order
                                    </Label>
                                    <Input
                                        id="sort_order"
                                        name="sort_order"
                                        type="number"
                                        min={0}
                                        step={1}
                                        required
                                        defaultValue={plan.sortOrder}
                                        className="tabular-nums"
                                    />
                                    <InputError message={errors.sort_order} />
                                </div>
                            </SettingsSection>

                            <SettingsSection
                                title="Features"
                                description="What a company on this plan can use. An empty limit means unlimited."
                            >
                                <PlanFeatureFields
                                    features={plan.features}
                                    errors={errors}
                                />
                                <InputError message={errors.features} />
                            </SettingsSection>

                            <div className="flex justify-end gap-2">
                                <Button
                                    nativeButton={false}
                                    variant="outline"
                                    render={<Link href={index()} />}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    Save plan
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
