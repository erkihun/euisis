import { Head, Link, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { useLocale } from '@/hooks/useLocale';

interface InstitutionOption {
    id: string;
    name_en: string;
    name_am: string | null;
    code: string;
}

interface ParentOfficeOption {
    id: string;
    name_en: string | null;
    name_am: string | null;
    office_code: string;
    office_level: string;
}

interface LevelOption {
    value: string;
    label: string;
}

interface Props {
    institutions: InstitutionOption[];
    selectedInstitution: InstitutionOption | null;
    parentOfficeOptions: ParentOfficeOption[];
    geographicOrgs: InstitutionOption[];
    levelOptions: LevelOption[];
    statusOptions: LevelOption[];
}

export default function InstitutionOfficesCreate({
    institutions,
    selectedInstitution,
    parentOfficeOptions,
    geographicOrgs,
    levelOptions,
    statusOptions,
}: Props) {
    const { t } = useLocale();

    const { data, setData, post, processing, errors } = useForm({
        institution_id: selectedInstitution?.id ?? '',
        geographic_organization_id: '',
        parent_office_id: '',
        office_level: '',
        office_code: '',
        name_en: '',
        name_am: '',
        short_name_en: '',
        short_name_am: '',
        assigned_scope_type: 'self' as 'self' | 'subtree',
        is_head_office: false,
        status: 'active',
        opened_on: '',
        closed_on: '',
        address_en: '',
        address_am: '',
        phone_number: '',
        email: '',
        notes: '',
    });

    const inputCls =
        'w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100';

    function handleInstitutionChange(institutionId: string) {
        setData('institution_id', institutionId);
        setData('parent_office_id', '');
        if (institutionId) {
            router.reload({
                data: { institution_id: institutionId },
                only: ['parentOfficeOptions'],
            });
        }
    }

    function handleSubmit(e: React.FormEvent) {
        e.preventDefault();
        post(route('institution-offices.store'));
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('institutionOffices.create')}
                    backHref={route('institution-offices.index')}
                />
            }
        >
            <Head title={t('institutionOffices.create')} />

            <form onSubmit={handleSubmit} className="mx-auto max-w-3xl space-y-6">
                <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 font-semibold text-gray-900 dark:text-slate-100">
                        {t('institutionOffices.institution')}
                    </h3>

                    <div className="grid gap-4 sm:grid-cols-2">
                        {/* Institution */}
                        <div className="sm:col-span-2">
                            <InputLabel htmlFor="institution_id" value={t('institutionOffices.institution')} />
                            <select
                                id="institution_id"
                                value={data.institution_id}
                                onChange={(e) => handleInstitutionChange(e.target.value)}
                                className={inputCls}
                                required
                            >
                                <option value="">{t('institutionOffices.selectInstitution')}</option>
                                {institutions.map((inst) => (
                                    <option key={inst.id} value={inst.id}>
                                        {inst.name_en} ({inst.code})
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.institution_id} />
                        </div>

                        {/* Office Level */}
                        <div>
                            <InputLabel htmlFor="office_level" value={t('institutionOffices.officeLevel')} />
                            <select
                                id="office_level"
                                value={data.office_level}
                                onChange={(e) => setData('office_level', e.target.value)}
                                className={inputCls}
                                required
                            >
                                <option value="">—</option>
                                {levelOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.office_level} />
                        </div>

                        {/* Office Code */}
                        <div>
                            <InputLabel htmlFor="office_code" value={t('institutionOffices.officeCode')} />
                            <TextInput
                                id="office_code"
                                value={data.office_code}
                                onChange={(e) => setData('office_code', e.target.value)}
                                className="w-full"
                                required
                                maxLength={50}
                            />
                            <InputError message={errors.office_code} />
                        </div>
                    </div>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 font-semibold text-gray-900 dark:text-slate-100">
                        {t('institutionOffices.officeName')}
                    </h3>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="name_en" value={t('institutionOffices.nameEn')} />
                            <TextInput
                                id="name_en"
                                value={data.name_en}
                                onChange={(e) => setData('name_en', e.target.value)}
                                className="w-full"
                                required
                            />
                            <InputError message={errors.name_en} />
                        </div>

                        <div>
                            <InputLabel htmlFor="name_am" value={t('institutionOffices.nameAm')} />
                            <TextInput
                                id="name_am"
                                value={data.name_am}
                                onChange={(e) => setData('name_am', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.name_am} />
                        </div>

                        <div>
                            <InputLabel htmlFor="short_name_en" value={t('institutionOffices.shortNameEn')} />
                            <TextInput
                                id="short_name_en"
                                value={data.short_name_en}
                                onChange={(e) => setData('short_name_en', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.short_name_en} />
                        </div>

                        <div>
                            <InputLabel htmlFor="short_name_am" value={t('institutionOffices.shortNameAm')} />
                            <TextInput
                                id="short_name_am"
                                value={data.short_name_am}
                                onChange={(e) => setData('short_name_am', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.short_name_am} />
                        </div>
                    </div>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 font-semibold text-gray-900 dark:text-slate-100">
                        {t('institutionOffices.parentOffice')}
                    </h3>

                    <div className="grid gap-4 sm:grid-cols-2">
                        {/* Head Office toggle — hides parent when true */}
                        <div className="sm:col-span-2 flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="is_head_office"
                                checked={data.is_head_office}
                                onChange={(e) => {
                                    setData('is_head_office', e.target.checked);
                                    if (e.target.checked) setData('parent_office_id', '');
                                }}
                                className="h-4 w-4 rounded border-gray-300"
                            />
                            <label htmlFor="is_head_office" className="text-sm font-medium text-gray-700 dark:text-slate-300">
                                {t('institutionOffices.isHeadOffice')}
                            </label>
                            <InputError message={errors.is_head_office} />
                        </div>

                        {!data.is_head_office && (
                            <div className="sm:col-span-2">
                                <InputLabel htmlFor="parent_office_id" value={t('institutionOffices.parentOffice')} />
                                <select
                                    id="parent_office_id"
                                    value={data.parent_office_id}
                                    onChange={(e) => setData('parent_office_id', e.target.value)}
                                    className={inputCls}
                                >
                                    <option value="">{t('institutionOffices.noParent')}</option>
                                    {parentOfficeOptions.map((opt) => (
                                        <option key={opt.id} value={opt.id}>
                                            {opt.name_en ?? opt.office_code} [{opt.office_level}]
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.parent_office_id} />
                            </div>
                        )}

                        {/* Geographic Area */}
                        <div className="sm:col-span-2">
                            <InputLabel htmlFor="geographic_organization_id" value={t('institutionOffices.geographicArea')} />
                            <select
                                id="geographic_organization_id"
                                value={data.geographic_organization_id}
                                onChange={(e) => setData('geographic_organization_id', e.target.value)}
                                className={inputCls}
                            >
                                <option value="">—</option>
                                {geographicOrgs.map((org) => (
                                    <option key={org.id} value={org.id}>
                                        {org.name_en} ({org.code})
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.geographic_organization_id} />
                        </div>
                    </div>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 font-semibold text-gray-900 dark:text-slate-100">
                        {t('institutionOffices.status')}
                    </h3>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="status" value={t('institutionOffices.status')} />
                            <select
                                id="status"
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className={inputCls}
                            >
                                {statusOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                    </option>
                                ))}
                            </select>
                            <InputError message={errors.status} />
                        </div>

                        <div>
                            <InputLabel htmlFor="assigned_scope_type" value={t('institutionOffices.assignedScopeType')} />
                            <select
                                id="assigned_scope_type"
                                value={data.assigned_scope_type}
                                onChange={(e) => setData('assigned_scope_type', e.target.value as 'self' | 'subtree')}
                                className={inputCls}
                            >
                                <option value="self">{t('institutionOffices.scopeSelf')}</option>
                                <option value="subtree">{t('institutionOffices.scopeSubtree')}</option>
                            </select>
                            <InputError message={errors.assigned_scope_type} />
                        </div>

                        <div>
                            <InputLabel htmlFor="opened_on" value={t('institutionOffices.openedOn')} />
                            <TextInput
                                type="date"
                                id="opened_on"
                                value={data.opened_on}
                                onChange={(e) => setData('opened_on', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.opened_on} />
                        </div>

                        <div>
                            <InputLabel htmlFor="closed_on" value={t('institutionOffices.closedOn')} />
                            <TextInput
                                type="date"
                                id="closed_on"
                                value={data.closed_on}
                                onChange={(e) => setData('closed_on', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.closed_on} />
                        </div>
                    </div>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="mb-4 font-semibold text-gray-900 dark:text-slate-100">
                        {t('institutionOffices.address')}
                    </h3>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="address_en" value={t('institutionOffices.addressEn')} />
                            <TextInput
                                id="address_en"
                                value={data.address_en}
                                onChange={(e) => setData('address_en', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.address_en} />
                        </div>

                        <div>
                            <InputLabel htmlFor="address_am" value={t('institutionOffices.addressAm')} />
                            <TextInput
                                id="address_am"
                                value={data.address_am}
                                onChange={(e) => setData('address_am', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.address_am} />
                        </div>

                        <div>
                            <InputLabel htmlFor="phone_number" value={t('institutionOffices.phone')} />
                            <TextInput
                                id="phone_number"
                                value={data.phone_number}
                                onChange={(e) => setData('phone_number', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.phone_number} />
                        </div>

                        <div>
                            <InputLabel htmlFor="email" value={t('institutionOffices.email')} />
                            <TextInput
                                type="email"
                                id="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="w-full"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="sm:col-span-2">
                            <InputLabel htmlFor="notes" value={t('institutionOffices.notes')} />
                            <textarea
                                id="notes"
                                value={data.notes}
                                onChange={(e) => setData('notes', e.target.value)}
                                rows={3}
                                className={inputCls}
                            />
                            <InputError message={errors.notes} />
                        </div>
                    </div>
                </section>

                <div className="flex items-center justify-end gap-3">
                    <Link
                        href={route('institution-offices.index')}
                        className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        {t('common.cancel')}
                    </Link>
                    <PrimaryButton disabled={processing}>{t('common.save')}</PrimaryButton>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
