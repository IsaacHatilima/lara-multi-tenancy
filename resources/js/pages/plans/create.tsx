import { Form, Head, Link } from '@inertiajs/react';
import PlanController from '@/actions/App/Http/Controllers/Plans/PlanController';
import InputError from '@/components/input-error';
import { SettingsSection } from '@/components/settings-section';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { index } from '@/routes/plans';
import type { IntervalOption, Plan } from './types';

type Props = {
    plans: Plan[];
    intervals: IntervalOption[];
    defaultSortOrder: number;
};

export default function CreatePlan({
    plans,
    intervals,
    defaultSortOrder,
}: Props) {
    const startFromItems = [
        { value: null, label: 'None' },
        ...plans.map((plan) => ({ value: plan.id, label: plan.name })),
    ];

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
                            description="The code identifies the plan. Only one active plan can have each code."
                        >
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="code">Code</Label>
                                    <Input
                                        id="code"
                                        name="code"
                                        required
                                        autoFocus
                                        autoComplete="off"
                                        spellCheck={false}
                                        placeholder="team"
                                        className="font-mono"
                                    />
                                    <InputError message={errors.code} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        autoComplete="off"
                                        placeholder="Team"
                                    />
                                    <InputError message={errors.name} />
                                </div>
                            </div>
                            <div className="grid gap-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea id="description" name="description" />
                                <InputError message={errors.description} />
                            </div>
                        </SettingsSection>

                        <SettingsSection
                            title="Price"
                            description="What a company pays each billing interval, and how long it can try the plan first."
                        >
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="price">Price</Label>
                                    <Input
                                        id="price"
                                        name="price"
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        required
                                        placeholder="29.00"
                                        className="tabular-nums"
                                    />
                                    <InputError message={errors.price} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="currency">Currency</Label>
                                    <Input
                                        id="currency"
                                        name="currency"
                                        required
                                        maxLength={3}
                                        autoComplete="off"
                                        spellCheck={false}
                                        placeholder="EUR"
                                        className="font-mono uppercase"
                                    />
                                    <InputError message={errors.currency} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="interval">
                                        Billing interval
                                    </Label>
                                    <Select
                                        name="interval"
                                        items={intervals}
                                        required
                                    >
                                        <SelectTrigger
                                            id="interval"
                                            className="w-full"
                                        >
                                            <SelectValue placeholder="Select an interval" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {intervals.map((interval) => (
                                                <SelectItem
                                                    key={interval.value}
                                                    value={interval.value}
                                                >
                                                    {interval.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.interval} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="trial_days">
                                        Trial days
                                    </Label>
                                    <Input
                                        id="trial_days"
                                        name="trial_days"
                                        type="number"
                                        min={0}
                                        step={1}
                                        required
                                        defaultValue={0}
                                        className="tabular-nums"
                                    />
                                    <InputError message={errors.trial_days} />
                                </div>
                            </div>
                        </SettingsSection>

                        <SettingsSection
                            title="Listing"
                            description="A public plan is offered to every company. Plans are listed by display order."
                        >
                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="is_public"
                                    name="is_public"
                                    value="1"
                                />
                                <Label htmlFor="is_public">Public</Label>
                            </div>
                            <InputError message={errors.is_public} />
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
                                    defaultValue={defaultSortOrder}
                                    className="tabular-nums"
                                />
                                <InputError message={errors.sort_order} />
                            </div>
                        </SettingsSection>

                        <SettingsSection
                            title="Features"
                            description="Copy the features and limits of another plan. Without one, the plan starts with no features."
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="start_from">Start from</Label>
                                <Select
                                    name="start_from"
                                    items={startFromItems}
                                    defaultValue={null}
                                >
                                    <SelectTrigger
                                        id="start_from"
                                        className="w-full"
                                    >
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value={null}>
                                            None
                                        </SelectItem>
                                        {plans.map((plan) => (
                                            <SelectItem
                                                key={plan.id}
                                                value={plan.id}
                                            >
                                                {plan.name}
                                                <span className="text-muted-foreground">
                                                    {startFromDetails(plan)}
                                                </span>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.start_from} />
                            </div>
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

/** Tells apart plans that share a name: the code, a custom plan's company, and an inactive version. */
function startFromDetails(plan: Plan): string {
    return [
        plan.code,
        plan.tenantName,
        plan.status === 'inactive' ? plan.statusLabel : null,
    ]
        .filter((detail) => detail !== null)
        .join(', ');
}
