import { Form, Head, Link } from '@inertiajs/react';
import PlanController from '@/actions/App/Http/Controllers/Plans/PlanController';
import { ConfirmDeleteDialog } from '@/components/confirm-delete-dialog';
import InputError from '@/components/input-error';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/plans';
import { PlanFeatureFields } from './components/plan-feature-fields';
import { PlanFields } from './components/plan-fields';
import { PlanPricingFields } from './components/plan-pricing-fields';
import type {
    BillingIntervalOption,
    CurrencyOption,
    Plan,
    PlanFeature,
} from './types';

type Props = {
    plan: Plan;
    features: PlanFeature[];
    currencies: CurrencyOption[];
    intervals: BillingIntervalOption[];
};

export default function EditPlan({
    plan,
    features,
    currencies,
    intervals,
}: Props) {
    return (
        <>
            <Head title={plan.name} />

            <div className="mx-auto flex w-full max-w-160 flex-col gap-6">
                <header className="flex flex-col gap-1">
                    <h1 className="text-xl font-semibold">{plan.name}</h1>
                    <p className="font-mono text-xs text-muted-foreground">
                        {plan.code}
                    </p>
                </header>

                <Form
                    {...PlanController.update.form(plan.id)}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <SettingsSection title="Plan">
                                <PlanFields defaults={plan} errors={errors} />
                            </SettingsSection>

                            <SettingsSection
                                title="Pricing"
                                description="Set the price to 0 for a free plan."
                            >
                                <PlanPricingFields
                                    defaults={plan}
                                    currencies={currencies}
                                    intervals={intervals}
                                    errors={errors}
                                />
                            </SettingsSection>

                            <SettingsSection
                                title="Features"
                                description="What a company on this plan can use. An empty limit means unlimited."
                            >
                                <PlanFeatureFields
                                    features={features}
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

                <SettingsSection
                    title="Delete plan"
                    description="The plan and its features are removed."
                >
                    <div>
                        <ConfirmDeleteDialog
                            form={PlanController.destroy.form(plan.id)}
                            title={`Delete ${plan.name}?`}
                            description="The plan and the features it includes are removed. This can't be undone."
                            confirmLabel="Delete plan"
                        />
                    </div>
                </SettingsSection>
            </div>
        </>
    );
}
