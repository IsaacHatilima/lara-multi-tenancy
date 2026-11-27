import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { BillingIntervalOption, CurrencyOption, Plan } from '../types';

type Props = {
    defaults: Pick<Plan, 'priceMinor' | 'currency' | 'interval' | 'trialDays'>;
    currencies: CurrencyOption[];
    intervals: BillingIntervalOption[];
    errors: Partial<Record<string, string>>;
};

export function PlanPricingFields({
    defaults,
    currencies,
    intervals,
    errors,
}: Props) {
    return (
        <>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="price">Price</Label>
                    <Input
                        id="price"
                        name="price"
                        type="number"
                        inputMode="decimal"
                        min="0"
                        step="0.01"
                        required
                        defaultValue={(defaults.priceMinor / 100).toFixed(2)}
                        className="tabular-nums"
                    />
                    <InputError message={errors.price} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="currency">Currency</Label>
                    <Select
                        name="currency"
                        items={currencies}
                        defaultValue={defaults.currency}
                    >
                        <SelectTrigger id="currency" className="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {currencies.map((currency) => (
                                <SelectItem
                                    key={currency.value}
                                    value={currency.value}
                                >
                                    {currency.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <InputError message={errors.currency} />
                </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="interval">Billed</Label>
                    <Select
                        name="interval"
                        items={intervals}
                        defaultValue={defaults.interval}
                    >
                        <SelectTrigger id="interval" className="w-full">
                            <SelectValue />
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
                    <Label htmlFor="trial_days">Trial days</Label>
                    <Input
                        id="trial_days"
                        name="trial_days"
                        type="number"
                        inputMode="numeric"
                        min="0"
                        step="1"
                        required
                        defaultValue={defaults.trialDays}
                        className="tabular-nums"
                    />
                    <InputError message={errors.trial_days} />
                </div>
            </div>
        </>
    );
}
