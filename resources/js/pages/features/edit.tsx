import { Form, Head, Link } from '@inertiajs/react';
import FeatureController from '@/actions/App/Http/Controllers/Features/FeatureController';
import { ConfirmDeleteDialog } from '@/components/confirm-delete-dialog';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/features';
import { FeatureFields } from './components/feature-fields';
import type { Feature, LimitUnitOption } from './types';

type Props = {
    feature: Feature;
    limitUnits: LimitUnitOption[];
};

export default function EditFeature({ feature, limitUnits }: Props) {
    return (
        <>
            <Head title={feature.name} />

            <div className="mx-auto flex w-full max-w-160 flex-col gap-6">
                <header className="flex flex-col gap-1">
                    <h1 className="text-xl font-semibold">{feature.name}</h1>
                    <p className="font-mono text-xs text-muted-foreground">
                        {feature.key}
                    </p>
                </header>

                <Form
                    {...FeatureController.update.form(feature.id)}
                    options={{ preserveScroll: true }}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <SettingsSection
                                title="Feature"
                                description="The key can't be changed, because code checks the feature by it."
                            >
                                <FeatureFields
                                    defaults={feature}
                                    limitUnits={limitUnits}
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
                                    Save feature
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <SettingsSection
                    title="Delete feature"
                    description="Every plan that includes it loses it."
                >
                    <div>
                        <ConfirmDeleteDialog
                            form={FeatureController.destroy.form(feature.id)}
                            title={`Delete ${feature.name}?`}
                            description="The feature is removed from every plan that includes it. This can't be undone."
                            confirmLabel="Delete feature"
                        />
                    </div>
                </SettingsSection>
            </div>
        </>
    );
}
