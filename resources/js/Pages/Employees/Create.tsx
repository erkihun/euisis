import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent, useRef, useState } from 'react';
import { useLocale } from '@/hooks/useLocale';
import CodeRuleField from '@/Components/code-rules/CodeRuleField';
import LocalizedDatePicker from '@/Components/Calendar/LocalizedDatePicker';

type Option = {
    id: string;
    name_en?: string;
    name_am?: string | null;
    title_en?: string;
    title_am?: string | null;
    job_position_code?: string;
    code?: string | null;
    organization_id?: string | null;
    organization_unit_id?: string | null;
    version_name?: string;
    status?: string;
};

export default function EmployeesCreate({
    organizations,
    organizationUnits,
    hierarchyVersions,
    positions,
    selectedOrganizationId,
    selectedOrganizationUnitId,
    selectedPositionId,
}: {
    organizations: Option[];
    organizationUnits: Option[];
    hierarchyVersions: Option[];
    positions: Option[];
    selectedOrganizationId: string | null;
    selectedOrganizationUnitId: string | null;
    selectedPositionId: string | null;
}) {
    const { t } = useLocale();

    const form = useForm<{
        employee_number: string;
        first_name: string;
        middle_name: string;
        last_name: string;
        national_id: string;
        phone: string;
        email: string;
        date_of_birth: string;
        gender: string;
        status: string;
        organization_id: string;
        organization_unit_id: string;
        hierarchy_version_id: string;
        position_id: string;
        position_title: string;
        effective_from: string;
        reason: string;
        photo: File | null;
    }>({
        employee_number: '',
        first_name: '',
        middle_name: '',
        last_name: '',
        national_id: '',
        phone: '',
        email: '',
        date_of_birth: '',
        gender: '',
        status: 'active',
        organization_id: selectedOrganizationId ?? organizations[0]?.id ?? '',
        organization_unit_id: selectedOrganizationUnitId ?? '',
        hierarchy_version_id: hierarchyVersions[0]?.id ?? '',
        position_id: selectedPositionId ?? '',
        position_title: '',
        effective_from: new Date().toISOString().slice(0, 10),
        reason: '',
        photo: null,
    });

    const [photoPreview, setPhotoPreview] = useState<string | null>(null);
    const photoInputRef = useRef<HTMLInputElement>(null);

    function handleNationalIdChange(raw: string) {
        const digits = raw.replace(/\D/g, '').slice(0, 16);
        form.setData('national_id', digits);
    }

    function formatNationalId(digits: string): string {
        return digits.replace(/(.{4})/g, '$1 ').trim();
    }

    function handlePhotoChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] ?? null;
        form.setData('photo', file);
        setPhotoPreview(file ? URL.createObjectURL(file) : null);
    }

    const inputCls =
        'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500';
    const selectedOrg = organizations.find((organization) => organization.id === form.data.organization_id);
    const filteredOrganizationUnits = organizationUnits.filter((unit) => unit.organization_id === form.data.organization_id);
    const filteredPositions = positions.filter((position) => {
        if (position.organization_id !== form.data.organization_id) {
            return false;
        }

        return form.data.organization_unit_id === '' || position.organization_unit_id === form.data.organization_unit_id;
    });

    function changeOrganization(organizationId: string) {
        form.setData({
            ...form.data,
            organization_id: organizationId,
            organization_unit_id: '',
            position_id: '',
        });
    }

    function changeOrganizationUnit(organizationUnitId: string) {
        form.setData({
            ...form.data,
            organization_unit_id: organizationUnitId,
            position_id: '',
        });
    }

    function changePosition(positionId: string) {
        const position = positions.find((candidate) => candidate.id === positionId);

        form.setData({
            ...form.data,
            position_id: positionId,
            organization_unit_id: form.data.organization_unit_id || position?.organization_unit_id || '',
        });
    }

    function submit(e: FormEvent<HTMLFormElement>) {
        e.preventDefault();
        form.post(route('employees.store'));
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('employees.createEmployee')}
                    actions={
                        <Link
                            href={route('employees.index')}
                            className="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            {t('common.back')}
                        </Link>
                    }
                />
            }
        >
            <Head title={t('employees.createEmployee')} />

            <form
                className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                onSubmit={submit}
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <CodeRuleField
                            entityType="employee"
                            context={{
                                organization_id: form.data.organization_id || undefined,
                                organization_unit_id: form.data.organization_unit_id || undefined,
                            }}
                            value={form.data.employee_number}
                            onChange={(v) => form.setData('employee_number', v)}
                            fieldName="employee_number"
                            label={t('employees.employeeNumber')}
                            canManualOverride={false}
                            error={form.errors.employee_number}
                        />
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.nationalId')}
                        </label>
                        <input
                            className={inputCls}
                            placeholder="XXXX XXXX XXXX XXXX"
                            inputMode="numeric"
                            value={formatNationalId(form.data.national_id)}
                            onChange={(e) => handleNationalIdChange(e.target.value)}
                            maxLength={19}
                        />
                        {form.errors.national_id && (
                            <p className="mt-1 text-xs text-red-600">{form.errors.national_id}</p>
                        )}
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.firstName')}
                        </label>
                        <input
                            className={inputCls}
                            placeholder={t('employees.firstName')}
                            value={form.data.first_name}
                            onChange={(e) => form.setData('first_name', e.target.value)}
                        />
                        {form.errors.first_name && (
                            <p className="mt-1 text-xs text-red-600">{form.errors.first_name}</p>
                        )}
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.middleName')}
                        </label>
                        <input
                            className={inputCls}
                            placeholder={t('employees.middleName')}
                            value={form.data.middle_name}
                            onChange={(e) => form.setData('middle_name', e.target.value)}
                        />
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.lastName')}
                        </label>
                        <input
                            className={inputCls}
                            placeholder={t('employees.lastName')}
                            value={form.data.last_name}
                            onChange={(e) => form.setData('last_name', e.target.value)}
                        />
                        {form.errors.last_name && (
                            <p className="mt-1 text-xs text-red-600">{form.errors.last_name}</p>
                        )}
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.phone')}
                        </label>
                        <input
                            className={inputCls}
                            placeholder="+251 9XX XXX XXX"
                            value={form.data.phone}
                            onChange={(e) => form.setData('phone', e.target.value)}
                        />
                    </div>

                    <div className="sm:col-span-2">
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.email')}
                        </label>
                        <input
                            className={inputCls}
                            type="email"
                            placeholder="employee@example.com"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                        />
                    </div>

                    <div className="sm:col-span-2">
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.photo')}
                        </label>
                        <div className="flex items-start gap-4">
                            {photoPreview ? (
                                <div className="relative flex-shrink-0">
                                    <img
                                        src={photoPreview}
                                        alt="preview"
                                        className="h-20 w-16 rounded-lg border border-gray-200 object-cover dark:border-slate-700"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => {
                                            form.setData('photo', null);
                                            setPhotoPreview(null);
                                            if (photoInputRef.current) photoInputRef.current.value = '';
                                        }}
                                        className="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-xs hover:bg-red-600"
                                    >
                                        ×
                                    </button>
                                </div>
                            ) : (
                                <div className="flex h-20 w-16 flex-shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 text-gray-400 dark:border-slate-600 dark:text-slate-500">
                                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            )}
                            <div className="flex-1">
                                <input
                                    ref={photoInputRef}
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    onChange={handlePhotoChange}
                                    className="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-blue-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300"
                                />
                                <p className="mt-1 text-xs text-gray-400 dark:text-slate-500">
                                    JPG, PNG or WEBP — max 4 MB
                                </p>
                                {form.errors.photo && (
                                    <p className="mt-1 text-xs text-red-600">{form.errors.photo}</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.dateOfBirth')}
                        </label>
                        <LocalizedDatePicker
                            className={inputCls}
                            value={form.data.date_of_birth}
                            onChange={(iso) => form.setData('date_of_birth', iso)}
                        />
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.gender')}
                        </label>
                        <select
                            className={inputCls}
                            value={form.data.gender}
                            onChange={(e) => form.setData('gender', e.target.value)}
                        >
                            <option value="">{t('employees.gender')}</option>
                            <option value="male">{t('employees.male')}</option>
                            <option value="female">{t('employees.female')}</option>
                        </select>
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('organizations.title')}
                        </label>
                        <select
                            className={inputCls}
                            value={form.data.organization_id}
                            onChange={(e) => changeOrganization(e.target.value)}
                            disabled={selectedOrganizationId !== null}
                        >
                            {selectedOrganizationId ? (
                                <option value={form.data.organization_id}>{selectedOrg?.name_en ?? t('employees.selectedOrganization')}</option>
                            ) : (
                                <>
                                    <option value="">{t('common.unassigned')}</option>
                                    {organizations.map((o) => (
                                        <option key={o.id} value={o.id}>{o.name_en}</option>
                                    ))}
                                </>
                            )}
                        </select>
                        {form.errors.organization_id && (
                            <p className="mt-1 text-xs text-red-600">{form.errors.organization_id}</p>
                        )}
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('positions.organizationUnit')}
                        </label>
                        <select
                            className={inputCls}
                            value={form.data.organization_unit_id}
                            onChange={(e) => changeOrganizationUnit(e.target.value)}
                            disabled={!form.data.organization_id}
                        >
                            <option value="">{t('positions.selectOrganizationUnit')}</option>
                            {filteredOrganizationUnits.map((unit) => (
                                <option key={unit.id} value={unit.id}>
                                    {unit.code ? `${unit.code} - ` : ''}{unit.name_en}
                                </option>
                            ))}
                        </select>
                        {form.errors.organization_unit_id && (
                            <p className="mt-1 text-xs text-red-600">{form.errors.organization_unit_id}</p>
                        )}
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('organizations.hierarchyVersion')}
                        </label>
                        <select
                            className={inputCls}
                            value={form.data.hierarchy_version_id}
                            onChange={(e) => form.setData('hierarchy_version_id', e.target.value)}
                        >
                            <option value="">{t('employees.noHierarchyVersion')}</option>
                            {hierarchyVersions.map((v) => (
                                <option key={v.id} value={v.id}>
                                    {v.version_name}{v.status ? ` (${v.status})` : ''}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('positions.title')}
                        </label>
                        <select
                            className={inputCls}
                            value={form.data.position_id}
                            onChange={(e) => changePosition(e.target.value)}
                            disabled={!form.data.organization_id}
                        >
                            <option value="">{t('employees.selectPosition')}</option>
                            {filteredPositions.map((pos) => (
                                <option key={pos.id} value={pos.id}>
                                    {pos.job_position_code ? `${pos.job_position_code} - ` : ''}{pos.title_en}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.orCreatePosition')}
                        </label>
                        <input
                            className={inputCls}
                            placeholder={t('employees.orCreatePosition')}
                            value={form.data.position_title}
                            onChange={(e) => form.setData('position_title', e.target.value)}
                        />
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('common.status')}
                        </label>
                        <select
                            className={inputCls}
                            value={form.data.status}
                            onChange={(e) => form.setData('status', e.target.value)}
                        >
                            <option value="active">{t('employees.active')}</option>
                            <option value="draft">{t('employees.draft')}</option>
                            <option value="suspended">{t('employees.suspended')}</option>
                        </select>
                    </div>

                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('common.effectiveFrom')}
                        </label>
                        <LocalizedDatePicker
                            className={inputCls}
                            value={form.data.effective_from}
                            onChange={(iso) => form.setData('effective_from', iso)}
                        />
                    </div>

                    <div className="sm:col-span-2">
                        <label className="mb-1 block text-xs font-medium text-gray-600 dark:text-slate-400">
                            {t('employees.assignmentReason')}
                        </label>
                        <textarea
                            className={`${inputCls} min-h-[6rem]`}
                            placeholder={t('employees.assignmentReason')}
                            value={form.data.reason}
                            onChange={(e) => form.setData('reason', e.target.value)}
                        />
                    </div>
                </div>

                <div className="mt-6 flex gap-3">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-60"
                    >
                        {form.processing ? t('common.saving') : t('employees.createEmployee')}
                    </button>
                    <Link
                        href={route('employees.index')}
                        className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        {t('common.cancel')}
                    </Link>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
