import { Form, Head, Link } from '@inertiajs/react';
import PlanController from '@/actions/App/Http/Controllers/Plans/PlanController';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/plans';
import { PlanFields } from './components/plan-fields';
import { PlanPricingFields } from './components/plan-pricing-fields';
import type { BillingIntervalOption, CurrencyOption } from './types';

type Props = {
    currencies: CurrencyOption[];
    intervals: BillingIntervalOption[];
};

export default function CreatePlan({ currencies, intervals }: Props) {
    return (
        <>
            <Head title="New plan" />
            <h1 className="sr-only">New plan</h1>

            <Form
                {...PlanController.store.form()}
                className="mx-auto flex w-full max-w-160 flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <SettingsSection
                            title="Plan"
                            description="Each plan has its own code. Choose its features after creating it."
                        >
                            <PlanFields
                                defaults={{
                                    name: '',
                                    code: '',
                                    description: null,
                                }}
                                errors={errors}
                            />
                        </SettingsSection>

                        <SettingsSection
                            title="Pricing"
                            description="Set the price to 0 for a free plan."
                        >
                            <PlanPricingFields
                                defaults={{
                                    priceMinor: 0,
                                    currency: currencies[0].value,
                                    interval: intervals[0].value,
                                    trialDays: 0,
                                }}
                                currencies={currencies}
                                intervals={intervals}
                                errors={errors}
                            />
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
                                Create plan
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}
