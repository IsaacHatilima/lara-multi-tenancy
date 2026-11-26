import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Tenant } from '../types';

type FieldName =
    | 'name'
    | 'legal_name'
    | 'email'
    | 'phone'
    | 'website'
    | 'registration_number'
    | 'tax_number'
    | 'address_line_1'
    | 'address_line_2'
    | 'city'
    | 'state'
    | 'postal_code'
    | 'country_code'
    | 'timezone'
    | 'locale';

type FieldSpec = {
    name: FieldName;
    label: string;
    value: (tenant: Tenant) => string | null;
    type?: 'email' | 'tel' | 'url';
    autoComplete?: string;
    placeholder?: string;
    maxLength?: number;
    wide?: boolean;
};

type FieldGroup = { legend: string; fields: FieldSpec[] };

const GROUPS: FieldGroup[] = [
    {
        legend: 'Contact',
        fields: [
            {
                name: 'legal_name',
                label: 'Legal name',
                value: (t) => t.legalName,
                autoComplete: 'organization',
                wide: true,
            },
            {
                name: 'email',
                label: 'Email',
                value: (t) => t.email,
                type: 'email',
                autoComplete: 'email',
            },
            {
                name: 'phone',
                label: 'Phone',
                value: (t) => t.phone,
                type: 'tel',
                autoComplete: 'tel',
            },
            {
                name: 'website',
                label: 'Website',
                value: (t) => t.website,
                type: 'url',
                placeholder: 'https://',
                autoComplete: 'url',
                wide: true,
            },
        ],
    },
    {
        legend: 'Registration',
        fields: [
            {
                name: 'registration_number',
                label: 'Registration number',
                value: (t) => t.registrationNumber,
            },
            {
                name: 'tax_number',
                label: 'Tax number',
                value: (t) => t.taxNumber,
            },
        ],
    },
    {
        legend: 'Address',
        fields: [
            {
                name: 'address_line_1',
                label: 'Address line 1',
                value: (t) => t.addressLine1,
                autoComplete: 'address-line1',
                wide: true,
            },
            {
                name: 'address_line_2',
                label: 'Address line 2',
                value: (t) => t.addressLine2,
                autoComplete: 'address-line2',
                wide: true,
            },
            {
                name: 'city',
                label: 'City',
                value: (t) => t.city,
                autoComplete: 'address-level2',
            },
            {
                name: 'state',
                label: 'State or region',
                value: (t) => t.state,
                autoComplete: 'address-level1',
            },
            {
                name: 'postal_code',
                label: 'Postal code',
                value: (t) => t.postalCode,
                autoComplete: 'postal-code',
            },
            {
                name: 'country_code',
                label: 'Country code',
                value: (t) => t.countryCode,
                placeholder: 'DE',
                maxLength: 2,
            },
        ],
    },
    {
        legend: 'Regional',
        fields: [
            {
                name: 'timezone',
                label: 'Time zone',
                value: (t) => t.timezone,
                placeholder: 'Europe/Berlin',
            },
            {
                name: 'locale',
                label: 'Locale',
                value: (t) => t.locale,
                placeholder: 'en',
            },
        ],
    },
];

type Props = {
    tenant?: Tenant;
    errors: Partial<Record<string, string>>;
};

/** The company's own details, shared by the create and edit forms; input names match the FormRequest. */
export function CompanyFields({ tenant, errors }: Props) {
    return (
        <>
            <div className="grid gap-2">
                <Label htmlFor="name">Company name</Label>
                <Input
                    id="name"
                    name="name"
                    defaultValue={tenant?.name}
                    required
                    autoComplete="organization"
                    placeholder="Acme"
                />
                <InputError message={errors.name} />
            </div>

            {GROUPS.map((group) => (
                <fieldset
                    key={group.legend}
                    className="grid gap-4 sm:grid-cols-2"
                >
                    <legend className="mb-3 text-label font-semibold tracking-[0.06em] text-muted-foreground uppercase">
                        {group.legend}
                    </legend>
                    {group.fields.map((field) => (
                        <div
                            key={field.name}
                            className={
                                field.wide
                                    ? 'grid gap-2 sm:col-span-2'
                                    : 'grid gap-2'
                            }
                        >
                            <Label htmlFor={field.name}>{field.label}</Label>
                            <Input
                                id={field.name}
                                name={field.name}
                                type={field.type ?? 'text'}
                                defaultValue={
                                    tenant ? (field.value(tenant) ?? '') : ''
                                }
                                autoComplete={field.autoComplete ?? 'off'}
                                placeholder={field.placeholder}
                                maxLength={field.maxLength}
                            />
                            <InputError message={errors[field.name]} />
                        </div>
                    ))}
                </fieldset>
            ))}
        </>
    );
}
