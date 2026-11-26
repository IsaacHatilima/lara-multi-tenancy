import { Form, Head, Link } from '@inertiajs/react';
import FeatureController from '@/actions/App/Http/Controllers/Features/FeatureController';
import InputError from '@/components/input-error';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/features';
import { FeatureFields } from './components/feature-fields';
import type { LimitUnitOption } from './types';

type Props = {
    limitUnits: LimitUnitOption[];
};

export default function CreateFeature({ limitUnits }: Props) {
    return (
        <>
            <Head title="New feature" />
            <h1 className="sr-only">New feature</h1>

            <Form
                {...FeatureController.store.form()}
                className="mx-auto flex w-full max-w-160 flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <SettingsSection
                            title="Feature"
                            description="Code checks the feature by its key, so the key can't be changed later. A limit unit lets each plan cap the feature."
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="key">Key</Label>
                                <Input
                                    id="key"
                                    name="key"
                                    required
                                    autoFocus
                                    autoComplete="off"
                                    spellCheck={false}
                                    placeholder="messaging"
                                    className="font-mono"
                                />
                                <InputError message={errors.key} />
                            </div>
                            <FeatureFields
                                defaults={{
                                    name: '',
                                    description: null,
                                    limitUnit: null,
                                }}
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
                                Create feature
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}
