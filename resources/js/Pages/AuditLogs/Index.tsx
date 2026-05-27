import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { useLocale } from '@/hooks/useLocale';
import PageHeader from '@/Components/PageHeader';
import LocalizedDatePicker from '@/Components/Calendar/LocalizedDatePicker';

type AuditLog = {
    id: string;
    event_type: string;
    actor_user_id?: string | null;
    auditable_type?: string | null;
    auditable_id?: string | null;
    old_values?: Record<string, unknown> | null;
    new_values?: Record<string, unknown> | null;
    created_at: string;
};

function ValuesDetail({
    old_values,
    new_values,
}: {
    old_values?: Record<string, unknown> | null;
    new_values?: Record<string, unknown> | null;
}) {
    const [open, setOpen] = useState(false);
    if (!old_values && !new_values) return null;
    return (
        <div className="mt-1">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className="text-xs text-blue-600 hover:underline dark:text-blue-400"
                aria-expanded={open}
            >
                {open ? '▲' : '▼'} {open ? 'Hide' : 'Show'}
            </button>
            {open && (
                <div className="mt-2 grid gap-2 sm:grid-cols-2">
                    {old_values && (
                        <div>
                            <p className="text-xs font-medium text-gray-500 dark:text-slate-400">
                                Before
                            </p>
                            <pre className="mt-1 overflow-x-auto rounded-lg bg-gray-100 p-2 text-xs text-gray-700 dark:bg-slate-800 dark:text-slate-300">
                                {JSON.stringify(old_values, null, 2)}
                            </pre>
                        </div>
                    )}
                    {new_values && (
                        <div>
                            <p className="text-xs font-medium text-gray-500 dark:text-slate-400">
                                After
                            </p>
                            <pre className="mt-1 overflow-x-auto rounded-lg bg-gray-100 p-2 text-xs text-gray-700 dark:bg-slate-800 dark:text-slate-300">
                                {JSON.stringify(new_values, null, 2)}
                            </pre>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

function formatEventType(event: string): string {
    return event
        .replace(/[._-]/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function AuditLogsIndex({
    auditLogs,
    filters,
}: {
    auditLogs: AuditLog[];
    filters?: { search?: string; event_type?: string; from?: string; to?: string };
}) {
    const { t } = useLocale();
    const filterForm = useForm({
        search: filters?.search ?? '',
        event_type: filters?.event_type ?? '',
        from: filters?.from ?? '',
        to: filters?.to ?? '',
    });

    const submitFilters = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        router.get(route('audit-logs.index'), filterForm.data, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const inputCls =
        'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500';

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('auditLogs.title')}
                    description={t('auditLogs.readOnly')}
                />
            }
        >
            <Head title={t('auditLogs.title')} />

            <div className="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                {/* Filters */}
                <div className="border-b border-gray-100 p-4 dark:border-slate-800">
                    <form className="flex flex-wrap items-end gap-3" onSubmit={submitFilters}>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('auditLogs.searchLogs')}
                            </label>
                            <input
                                className={inputCls}
                                placeholder={t('auditLogs.actor')}
                                value={filterForm.data.search}
                                onChange={(e) => filterForm.setData('search', e.target.value)}
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('auditLogs.event')}
                            </label>
                            <input
                                className={inputCls}
                                placeholder="e.g. card.issued"
                                value={filterForm.data.event_type}
                                onChange={(e) => filterForm.setData('event_type', e.target.value)}
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('common.effectiveFrom')}
                            </label>
                            <LocalizedDatePicker
                                className={inputCls}
                                value={filterForm.data.from}
                                onChange={(iso) => filterForm.setData('from', iso)}
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('common.effectiveTo')}
                            </label>
                            <LocalizedDatePicker
                                className={inputCls}
                                value={filterForm.data.to}
                                onChange={(iso) => filterForm.setData('to', iso)}
                            />
                        </div>
                        <button
                            type="submit"
                            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                        >
                            {t('common.filter')}
                        </button>
                    </form>
                </div>

                {/* Table */}
                {auditLogs.length === 0 ? (
                    <div className="p-6">
                        <EmptyState title={t('auditLogs.noLogs')} description="" />
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-left text-sm">
                            <thead className="bg-gray-50 dark:bg-slate-950">
                                <tr>
                                    {[
                                        t('auditLogs.event'),
                                        t('auditLogs.actor'),
                                        t('auditLogs.subject'),
                                        t('auditLogs.timestamp'),
                                        t('auditLogs.changes'),
                                    ].map((h) => (
                                        <th
                                            key={h}
                                            className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400"
                                        >
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {auditLogs.map((log) => (
                                    <tr
                                        key={log.id}
                                        className="border-t border-gray-100 text-gray-700 align-top dark:border-slate-800 dark:text-slate-200"
                                    >
                                        <td className="px-4 py-3">
                                            <StatusBadge
                                                status={log.event_type}
                                                label={formatEventType(log.event_type)}
                                            />
                                        </td>
                                        <td className="px-4 py-3 font-mono text-xs text-gray-500 dark:text-slate-400">
                                            {log.actor_user_id ?? 'system'}
                                        </td>
                                        <td className="px-4 py-3 text-xs text-gray-400 dark:text-slate-500">
                                            {log.auditable_type && (
                                                <span>
                                                    {log.auditable_type}
                                                    {log.auditable_id && (
                                                        <span className="font-mono">
                                                            {' '}
                                                            #{log.auditable_id}
                                                        </span>
                                                    )}
                                                </span>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-xs text-gray-400 dark:text-slate-500">
                                            {log.created_at}
                                        </td>
                                        <td className="px-4 py-3">
                                            <ValuesDetail
                                                old_values={log.old_values}
                                                new_values={log.new_values}
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
