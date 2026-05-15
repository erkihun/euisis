import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputLabel from '@/Components/InputLabel';
import PageHeader from '@/Components/PageHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLocale } from '@/hooks/useLocale';
import { toDateInput } from '@/lib/dateUtils';
import CodeRuleField from '@/Components/code-rules/CodeRuleField';

export default function PositionsEdit({ position, organizations }: { position: any; organizations: Array<{ id: string; name_en: string }> }) {
    const { t } = useLocale();
    const form = useForm({
        job_position_code: position.job_position_code ?? '',
        title_en: position.title_en ?? '',
        title_am: position.title_am ?? '',
        organization_id: position.organization_id ?? '',
        description_en: position.description_en ?? '',
        description_am: position.description_am ?? '',
        grade_level: position.grade_level ?? '',
        job_family: position.job_family ?? '',
        is_active: position.is_active ?? true,
        effective_from: toDateInput(position.effective_from),
        effective_to: toDateInput(position.effective_to),
    });
    const inputCls = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.patch(route('positions.update', position.id));
    }

    return (
        <AuthenticatedLayout header={<PageHeader title={t('positions.editPosition')} />}>
            <Head title={t('positions.editPosition')} />
            <form className="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" onSubmit={submit}>
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-1.5">
                        <CodeRuleField
                            entityType="position"
                            value={form.data.job_position_code}
                            onChange={(v) => form.setData('job_position_code', v)}
                            fieldName="job_position_code"
                            label={t('positions.jobPositionCode')}
                            canManualOverride={false}
                            existingCode={position.job_position_code ?? ''}
                            preserveExistingCodeOnEdit
                            error={form.errors.job_position_code}
                        />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="organization_id" value={t('positions.organization')} />
                        <select id="organization_id" className={inputCls} value={form.data.organization_id} onChange={(event) => form.setData('organization_id', event.target.value)}>
                            <option value="">{t('positions.organization')}</option>
                            {organizations.map((organization) => <option key={organization.id} value={organization.id}>{organization.name_en}</option>)}
                        </select>
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="title_en" value={t('positions.englishTitle')} />
                        <input id="title_en" className={inputCls} value={form.data.title_en} placeholder={t('positions.englishTitle')} onChange={(event) => form.setData('title_en', event.target.value)} />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="title_am" value={t('positions.amharicTitle')} />
                        <input id="title_am" className={inputCls} value={form.data.title_am} placeholder={t('positions.amharicTitle')} onChange={(event) => form.setData('title_am', event.target.value)} />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="grade_level" value={t('positions.gradeLevel')} />
                        <input id="grade_level" className={inputCls} value={form.data.grade_level} placeholder={t('positions.gradeLevel')} onChange={(event) => form.setData('grade_level', event.target.value)} />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="job_family" value={t('positions.jobFamily')} />
                        <input id="job_family" className={inputCls} value={form.data.job_family} placeholder={t('positions.jobFamily')} onChange={(event) => form.setData('job_family', event.target.value)} />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="effective_from" value={t('common.effectiveFrom')} />
                        <input id="effective_from" className={inputCls} type="date" value={form.data.effective_from} onChange={(event) => form.setData('effective_from', event.target.value)} />
                    </div>
                    <div className="space-y-1.5">
                        <InputLabel htmlFor="effective_to" value={t('common.effectiveTo')} />
                        <input id="effective_to" className={inputCls} type="date" value={form.data.effective_to} onChange={(event) => form.setData('effective_to', event.target.value)} />
                    </div>
                </div>
                <div className="space-y-1.5">
                    <InputLabel htmlFor="description_en" value={t('positions.positionDescription')} />
                    <textarea id="description_en" className={`${inputCls} min-h-28`} value={form.data.description_en} placeholder={t('positions.positionDescription')} onChange={(event) => form.setData('description_en', event.target.value)} />
                </div>
                <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                    <input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} />
                    {t('positions.activePosition')}
                </label>
                <div className="flex gap-3">
                    <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" type="submit" disabled={form.processing}>{t('common.save')}</button>
                    <Link href={route('positions.show', position.id)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200">{t('common.cancel')}</Link>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
