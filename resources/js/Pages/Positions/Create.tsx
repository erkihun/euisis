import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputLabel from '@/Components/InputLabel';
import PageHeader from '@/Components/PageHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLocale } from '@/hooks/useLocale';
import CodeRuleField from '@/Components/code-rules/CodeRuleField';
import LocalizedDatePicker from '@/Components/Calendar/LocalizedDatePicker';

type OccupationOption = { id: string; isco_code: string; name_en: string | null; name_am: string | null };
type UnitOption = { id: string; name_en: string; code: string; organization_unit_type_id: string | null };

interface Props {
    organizations: Array<{ id: string; name_en: string }>;
    organizationUnits: UnitOption[];
    occupations: OccupationOption[];
    gradeLevels: string[];
    selectedOrganizationId: string | null;
    selectedOrganizationUnitId: string | null;
}

export default function PositionsCreate({ organizations, organizationUnits, occupations, gradeLevels, selectedOrganizationId, selectedOrganizationUnitId }: Props) {
    const { t } = useLocale();
    const form = useForm({
        job_position_code: '',
        title_en: '',
        title_am: '',
        organization_id: selectedOrganizationId ?? '',
        organization_unit_id: selectedOrganizationUnitId ?? '',
        occupation_id: '',
        description_en: '',
        description_am: '',
        grade_level: '',
        job_family: '',
        is_active: true,
        effective_from: new Date().toISOString().slice(0, 10),
        effective_to: '',
    });

    const inputCls = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
    const errorCls = 'mt-1 text-xs text-red-600 dark:text-red-400';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post(route('positions.store'));
    }

    const cancelHref = selectedOrganizationId
        ? route('positions.index') +
          '?organization_id=' + selectedOrganizationId +
          (selectedOrganizationUnitId ? '&organization_unit_id=' + selectedOrganizationUnitId : '')
        : route('positions.index');

    const selectedOrg = organizations.find((o) => o.id === form.data.organization_id);
    const selectedUnit = organizationUnits.find((unit) => unit.id === form.data.organization_unit_id);

    // If an organization is selected but has no units, block form submission
    if (selectedOrganizationId && organizationUnits.length === 0) {
        return (
            <AuthenticatedLayout header={<PageHeader title={t('positions.createPosition')} />}>
                <Head title={t('positions.createPosition')} />
                <div className="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center dark:border-amber-800 dark:bg-amber-950/30">
                    <p className="text-base font-semibold text-amber-800 dark:text-amber-300">
                        {t('positions.noOrganizationUnits')}
                    </p>
                    <p className="mt-2 text-sm text-amber-700 dark:text-amber-400">
                        {t('positions.noOrganizationUnitsHint')}
                    </p>
                    <Link
                        href={cancelHref}
                        className="mt-4 inline-block rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200"
                    >
                        {t('common.back')}
                    </Link>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout header={<PageHeader title={t('positions.createPosition')} />}>
            <Head title={t('positions.createPosition')} />
            <form
                className="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                onSubmit={submit}
            >
                <div className="grid gap-4 md:grid-cols-2">
                    {/* Code */}
                    <div className="space-y-1.5">
                        <CodeRuleField
                            entityType="position"
                            context={{
                                organization_id: form.data.organization_id || undefined,
                                organization_unit_id: form.data.organization_unit_id || undefined,
                                organization_unit_type_id: selectedUnit?.organization_unit_type_id || undefined,
                            }}
                            value={form.data.job_position_code}
                            onChange={(v) => form.setData('job_position_code', v)}
                            fieldName="job_position_code"
                            label={t('positions.jobPositionCode')}
                            canManualOverride={false}
                            error={form.errors.job_position_code}
                        />
                    </div>

                    {/* Organization — read-only when pre-selected from tree */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="organization_id" value={t('positions.organization')} />
                        {selectedOrganizationId ? (
                            <>
                                <input type="hidden" name="organization_id" value={form.data.organization_id} />
                                <div className={`${inputCls} cursor-not-allowed bg-gray-50 text-gray-600 dark:bg-slate-900 dark:text-slate-400`}>
                                    {selectedOrg?.name_en ?? '—'}
                                </div>
                            </>
                        ) : (
                            <select
                                id="organization_id"
                                className={inputCls}
                                value={form.data.organization_id}
                                onChange={(e) => form.setData('organization_id', e.target.value)}
                            >
                                <option value="">{t('positions.organization')}</option>
                                {organizations.map((org) => (
                                    <option key={org.id} value={org.id}>{org.name_en}</option>
                                ))}
                            </select>
                        )}
                        {form.errors.organization_id && <p className={errorCls}>{form.errors.organization_id}</p>}
                    </div>

                    {/* Organization Unit (required) */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="organization_unit_id" value={t('positions.organizationUnit')} />
                        {selectedOrganizationUnitId ? (
                            <>
                                <input type="hidden" name="organization_unit_id" value={form.data.organization_unit_id} />
                                <div className={`${inputCls} cursor-not-allowed bg-gray-50 text-gray-600 dark:bg-slate-900 dark:text-slate-400`}>
                                    {(() => {
                                        const u = organizationUnits.find((u) => u.id === form.data.organization_unit_id);
                                        return u ? `${u.code} — ${u.name_en}` : '—';
                                    })()}
                                </div>
                            </>
                        ) : (
                            <select
                                id="organization_unit_id"
                                className={inputCls}
                                value={form.data.organization_unit_id}
                                onChange={(e) => form.setData('organization_unit_id', e.target.value)}
                                required
                            >
                                <option value="">{t('positions.selectOrganizationUnit')}</option>
                                {organizationUnits.map((unit) => (
                                    <option key={unit.id} value={unit.id}>
                                        {unit.code} — {unit.name_en}
                                    </option>
                                ))}
                            </select>
                        )}
                        {form.errors.organization_unit_id && <p className={errorCls}>{form.errors.organization_unit_id}</p>}
                    </div>

                    {/* Occupation (required) */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="occupation_id" value={t('positions.occupation')} />
                        <select
                            id="occupation_id"
                            className={inputCls}
                            value={form.data.occupation_id}
                            onChange={(e) => form.setData('occupation_id', e.target.value)}
                            required
                        >
                            <option value="">{t('positions.selectOccupation')}</option>
                            {occupations.map((occ) => (
                                <option key={occ.id} value={occ.id}>
                                    {occ.isco_code}{occ.name_en ? ` — ${occ.name_en}` : ''}
                                </option>
                            ))}
                        </select>
                        {form.errors.occupation_id && <p className={errorCls}>{form.errors.occupation_id}</p>}
                    </div>

                    {/* English Title */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="title_en" value={t('positions.englishTitle')} />
                        <input
                            id="title_en"
                            className={inputCls}
                            value={form.data.title_en}
                            placeholder={t('positions.englishTitle')}
                            onChange={(e) => form.setData('title_en', e.target.value)}
                        />
                        {form.errors.title_en && <p className={errorCls}>{form.errors.title_en}</p>}
                    </div>

                    {/* Amharic Title */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="title_am" value={t('positions.amharicTitle')} />
                        <input
                            id="title_am"
                            className={inputCls}
                            value={form.data.title_am}
                            placeholder={t('positions.amharicTitle')}
                            onChange={(e) => form.setData('title_am', e.target.value)}
                        />
                    </div>

                    {/* Grade Level */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="grade_level" value={t('positions.gradeLevel')} />
                        <select
                            id="grade_level"
                            className={inputCls}
                            value={form.data.grade_level}
                            onChange={(e) => form.setData('grade_level', e.target.value)}
                        >
                            <option value="">{t('positions.selectGradeLevel')}</option>
                            {gradeLevels.map((gl) => (
                                <option key={gl} value={gl}>{gl}</option>
                            ))}
                        </select>
                        {form.errors.grade_level && <p className={errorCls}>{form.errors.grade_level}</p>}
                    </div>

                    {/* Job Family */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="job_family" value={t('positions.jobFamily')} />
                        <input
                            id="job_family"
                            className={inputCls}
                            value={form.data.job_family}
                            placeholder={t('positions.jobFamily')}
                            onChange={(e) => form.setData('job_family', e.target.value)}
                        />
                    </div>

                    {/* Effective From */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="effective_from" value={t('common.effectiveFrom')} />
                        <LocalizedDatePicker
                            id="effective_from"
                            className={inputCls}
                            value={form.data.effective_from}
                            onChange={(iso) => form.setData('effective_from', iso)}
                        />
                    </div>

                    {/* Effective To */}
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="effective_to" value={t('common.effectiveTo')} />
                        <LocalizedDatePicker
                            id="effective_to"
                            className={inputCls}
                            value={form.data.effective_to}
                            onChange={(iso) => form.setData('effective_to', iso)}
                        />
                    </div>
                </div>

                {/* Description */}
                <div className="space-y-1.5">
                    <InputLabel htmlFor="description_en" value={t('positions.positionDescription')} />
                    <textarea
                        id="description_en"
                        className={`${inputCls} min-h-28`}
                        value={form.data.description_en}
                        placeholder={t('positions.positionDescription')}
                        onChange={(e) => form.setData('description_en', e.target.value)}
                    />
                </div>

                {/* Active */}
                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                    <input
                        type="checkbox"
                        checked={form.data.is_active}
                        onChange={(e) => form.setData('is_active', e.target.checked)}
                    />
                    {t('positions.activePosition')}
                </label>

                <div className="flex gap-3">
                    <button
                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                        type="submit"
                        disabled={form.processing}
                    >
                        {t('common.save')}
                    </button>
                    <Link
                        href={cancelHref}
                        className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200"
                    >
                        {t('common.cancel')}
                    </Link>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
