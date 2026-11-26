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
import { Textarea } from '@/components/ui/textarea';
import type { Feature, LimitUnitOption } from '../types';

type Props = {
    defaults: Pick<Feature, 'name' | 'description' | 'limitUnit'>;
    limitUnits: LimitUnitOption[];
    errors: Partial<Record<string, string>>;
};

/** The fields a feature keeps editable: everything but its key. */
export function FeatureFields({ defaults, limitUnits, errors }: Props) {
    const unitItems = [{ value: null, label: 'None' }, ...limitUnits];

    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    autoComplete="off"
                    defaultValue={defaults.name}
                    placeholder="Messaging"
                />
                <InputError message={errors.name} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="description">Description</Label>
                <Textarea
                    id="description"
                    name="description"
                    defaultValue={defaults.description ?? ''}
                />
                <InputError message={errors.description} />
            </div>
            <div className="grid gap-2 sm:max-w-1/2">
                <Label htmlFor="limit_unit">Limit unit</Label>
                <Select
                    name="limit_unit"
                    items={unitItems}
                    defaultValue={defaults.limitUnit}
                >
                    <SelectTrigger id="limit_unit" className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {unitItems.map((unit) => (
                            <SelectItem key={unit.label} value={unit.value}>
                                {unit.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.limit_unit} />
            </div>
        </>
    );
}
