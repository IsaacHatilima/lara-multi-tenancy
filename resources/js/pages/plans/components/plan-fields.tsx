import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { Plan } from '../types';

type Props = {
    defaults: Pick<Plan, 'name' | 'code' | 'description'>;
    errors: Partial<Record<string, string>>;
};

export function PlanFields({ defaults, errors }: Props) {
    return (
        <>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        name="name"
                        required
                        autoComplete="off"
                        defaultValue={defaults.name}
                        placeholder="Team"
                    />
                    <InputError message={errors.name} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="code">Code</Label>
                    <Input
                        id="code"
                        name="code"
                        required
                        autoComplete="off"
                        spellCheck={false}
                        defaultValue={defaults.code}
                        placeholder="team"
                        className="font-mono"
                    />
                    <InputError message={errors.code} />
                </div>
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
        </>
    );
}
