import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Head, Link, router } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';

export default function PositionsShow({ position }: { position: any }) {
    const { t } = useLocale();

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={`${position.job_position_code} · ${position.title_en}`}
                    actions={
                        <div className="flex gap-3">
                            {position.can?.update && <Link href={route('positions.edit', position.id)} className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">{t('common.edit')}</Link>}
                            {position.can?.archive && position.is_active && <button type="button" onClick={() => router.delete(route('positions.archive', position.id))} className="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">{t('positions.archivePosition')}</button>}
                            {position.can?.restore && !position.is_active && <button type="button" onClick={() => router.post(route('positions.restore', position.id))} className="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">{t('positions.restorePosition')}</button>}
                        </div>
                    }
                />
            }
        >
            <Head title={position.title_en} />
            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-2xl border border-gray-200 bg-white p-5 lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <div className="grid gap-4 md:grid-cols-2 text-sm">
                        <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('positions.jobPositionCode')}</div><div className="mt-1 font-mono">{position.job_position_code}</div></div>
                        <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('common.status')}</div><div className="mt-1"><StatusBadge status={position.is_active ? 'active' : 'inactive'} /></div></div>
                        <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('positions.organization')}</div><div className="mt-1">{position.organization?.name_en ?? '—'}</div></div>
                        <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('positions.gradeLevel')}</div><div className="mt-1">{position.grade_level ?? '—'}</div></div>
                        <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('positions.englishTitle')}</div><div className="mt-1">{position.title_en}</div></div>
                        <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('positions.amharicTitle')}</div><div className="mt-1">{position.title_am ?? '—'}</div></div>
                    </div>
                </section>
                <aside className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div className="text-xs text-gray-500 dark:text-slate-400">{t('common.count')}</div>
                    <div className="mt-1 text-2xl font-semibold text-gray-900 dark:text-slate-100">{position.assignments_count ?? 0}</div>
                </aside>
            </div>
        </AuthenticatedLayout>
    );
}
