import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';
import { useConfirm } from '@/hooks/useConfirm';

type PositionRow = {
    id: string;
    job_position_code: string;
    title_en: string;
    title_am: string | null;
    organization?: { id: string; name_en: string } | null;
    grade_level: string | null;
    job_family: string | null;
    is_active: boolean;
    effective_from: string | null;
    effective_to: string | null;
    can: { view: boolean; update: boolean; archive: boolean; restore: boolean };
};

export default function PositionsIndex({
    positions,
    organizations,
    filters,
    can,
}: {
    positions: PositionRow[];
    organizations: Array<{ id: string; name_en: string }>;
    filters: Record<string, string>;
    can: { create: boolean };
}) {
    const { t } = useLocale();
    const { confirm } = useConfirm();
    const form = useForm({
        search: filters.search ?? '',
        organization_id: filters.organization_id ?? '',
        job_family: filters.job_family ?? '',
        grade_level: filters.grade_level ?? '',
        is_active: filters.is_active ?? '',
    });

    const inputCls =
        'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(route('positions.index'), form.data, { preserveState: true, preserveScroll: true });
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('positions.title')}
                    actions={can.create ? (
                        <Link href={route('positions.create')} className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                            <Plus className="h-3.5 w-3.5" />
                            {t('positions.createPosition')}
                        </Link>
                    ) : undefined}
                />
            }
        >
            <Head title={t('positions.title')} />

            <div className="space-y-6">
                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <form className="grid gap-3 md:grid-cols-5" onSubmit={submit}>
                        <input className={inputCls} value={form.data.search} placeholder={t('positions.searchPositions')} onChange={(event) => form.setData('search', event.target.value)} />
                        <select className={inputCls} value={form.data.organization_id} onChange={(event) => form.setData('organization_id', event.target.value)}>
                            <option value="">{t('positions.organization')}</option>
                            {organizations.map((organization) => <option key={organization.id} value={organization.id}>{organization.name_en}</option>)}
                        </select>
                        <input className={inputCls} value={form.data.job_family} placeholder={t('positions.jobFamily')} onChange={(event) => form.setData('job_family', event.target.value)} />
                        <input className={inputCls} value={form.data.grade_level} placeholder={t('positions.gradeLevel')} onChange={(event) => form.setData('grade_level', event.target.value)} />
                        <div className="flex gap-3">
                            <select className={`${inputCls} flex-1`} value={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.value)}>
                                <option value="">{t('common.status')}</option>
                                <option value="1">{t('common.active')}</option>
                                <option value="0">{t('common.inactive')}</option>
                            </select>
                            <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" type="submit">
                                {t('common.filter')}
                            </button>
                        </div>
                    </form>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    {positions.length === 0 ? (
                        <div className="p-6">
                            <EmptyState title={t('positions.noPositionsFound')} />
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-left text-sm">
                                <thead className="bg-gray-50 dark:bg-slate-950">
                                    <tr>
                                        {[t('positions.jobPositionCode'), t('positions.englishTitle'), t('positions.amharicTitle'), t('positions.organization'), t('positions.gradeLevel'), t('common.status'), t('common.effectiveFrom'), ''].map((heading) => (
                                            <th key={heading} className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                                {heading}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {positions.map((position) => (
                                        <tr key={position.id} className="border-t border-gray-100 text-gray-700 dark:border-slate-800 dark:text-slate-200">
                                            <td className="px-4 py-3 font-mono text-xs">
                                                <Link href={route('positions.show', position.id)} className="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                                    {position.job_position_code}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3">{position.title_en}</td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">{position.title_am ?? '—'}</td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">{position.organization?.name_en ?? '—'}</td>
                                            <td className="px-4 py-3">{position.grade_level ?? '—'}</td>
                                            <td className="px-4 py-3"><StatusBadge status={position.is_active ? 'active' : 'inactive'} /></td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">{position.effective_from ?? '—'}</td>
                                            <td className="px-4 py-3">
                                                <div className="flex justify-end gap-3">
                                                    {position.can.update && <Link href={route('positions.edit', position.id)} className="text-xs font-medium text-blue-600 hover:text-blue-800">{t('common.edit')}</Link>}
                                                    {position.can.archive && position.is_active && (
                                                        <button
                                                            type="button"
                                                            onClick={async () => {
                                                                const { confirmed } = await confirm({
                                                                    title: t('confirmations.confirmDeleteTitle'),
                                                                    description: t('confirmations.thisRecordWillMoveToRecycleBin'),
                                                                    confirmLabel: t('confirmations.delete'),
                                                                    cancelLabel: t('confirmations.cancel'),
                                                                    variant: 'danger',
                                                                });
                                                                if (confirmed) router.delete(route('positions.archive', position.id));
                                                            }}
                                                            className="text-xs font-medium text-red-600 hover:text-red-800"
                                                        >
                                                            {t('positions.archivePosition')}
                                                        </button>
                                                    )}
                                                    {position.can.restore && !position.is_active && (
                                                        <button
                                                            type="button"
                                                            onClick={async () => {
                                                                const { confirmed } = await confirm({
                                                                    title: t('confirmations.confirmRestoreTitle'),
                                                                    description: t('confirmations.thisActionCannotBeUndone'),
                                                                    confirmLabel: t('confirmations.restore'),
                                                                    cancelLabel: t('confirmations.cancel'),
                                                                    variant: 'default',
                                                                });
                                                                if (confirmed) router.post(route('positions.restore', position.id));
                                                            }}
                                                            className="text-xs font-medium text-green-600 hover:text-green-800"
                                                        >
                                                            {t('positions.restorePosition')}
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
