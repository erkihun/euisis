import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

type TransferRow = {
    id: string;
    status: string;
    effective_date: string | null;
    employee?: { employee_number: string; full_name: string } | null;
    from_organization?: { name_en: string } | null;
    to_organization?: { name_en: string } | null;
    requested_by?: { name: string } | null;
};

export default function TransfersIndex({
    transfers,
    organizations,
    users,
    filters,
    can,
}: {
    transfers: TransferRow[];
    organizations: Array<{ id: string; name_en: string }>;
    users: Array<{ id: number; name: string }>;
    filters: Record<string, string>;
    can: { create: boolean };
}) {
    const { t } = useLocale();
    const form = useForm({
        search: filters.search ?? '',
        status: filters.status ?? '',
        from_organization_id: filters.from_organization_id ?? '',
        to_organization_id: filters.to_organization_id ?? '',
        requested_by: filters.requested_by ?? '',
        date_from: filters.date_from ?? '',
        date_to: filters.date_to ?? '',
    });
    const inputCls = 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(route('employee-transfers.index'), form.data, { preserveState: true, preserveScroll: true });
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('transfers.title')}
                    actions={can.create ? (
                        <Link href={route('employee-transfers.create')} className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">
                            <Plus className="h-3.5 w-3.5" />
                            {t('transfers.newTransfer')}
                        </Link>
                    ) : undefined}
                />
            }
        >
            <Head title={t('transfers.title')} />
            <div className="space-y-6">
                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <form className="grid gap-3 md:grid-cols-4 xl:grid-cols-7" onSubmit={submit}>
                        <input className={inputCls} placeholder={t('transfers.searchPlaceholder')} value={form.data.search} onChange={(event) => form.setData('search', event.target.value)} />
                        <select className={inputCls} value={form.data.status} onChange={(event) => form.setData('status', event.target.value)}>
                            <option value="">{t('transfers.transferStatus')}</option>
                            <option value="draft">{t('transfers.statusDraft')}</option>
                            <option value="submitted">{t('transfers.statusSubmitted')}</option>
                            <option value="current_organization_confirmed">{t('transfers.statusCurrentConfirmed')}</option>
                            <option value="receiving_organization_confirmed">{t('transfers.statusReceivingConfirmed')}</option>
                            <option value="approved">{t('transfers.statusApproved')}</option>
                            <option value="rejected">{t('transfers.statusRejected')}</option>
                            <option value="cancelled">{t('transfers.statusCancelled')}</option>
                            <option value="completed">{t('transfers.statusCompleted')}</option>
                        </select>
                        <select className={inputCls} value={form.data.from_organization_id} onChange={(event) => form.setData('from_organization_id', event.target.value)}>
                            <option value="">{t('transfers.fromOrganization')}</option>
                            {organizations.map((organization) => <option key={organization.id} value={organization.id}>{organization.name_en}</option>)}
                        </select>
                        <select className={inputCls} value={form.data.to_organization_id} onChange={(event) => form.setData('to_organization_id', event.target.value)}>
                            <option value="">{t('transfers.toOrganization')}</option>
                            {organizations.map((organization) => <option key={organization.id} value={organization.id}>{organization.name_en}</option>)}
                        </select>
                        <select className={inputCls} value={form.data.requested_by} onChange={(event) => form.setData('requested_by', event.target.value)}>
                            <option value="">{t('transfers.requestedBy')}</option>
                            {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                        </select>
                        <input className={inputCls} type="date" value={form.data.date_from} onChange={(event) => form.setData('date_from', event.target.value)} />
                        <div className="flex gap-3">
                            <input className={`${inputCls} flex-1`} type="date" value={form.data.date_to} onChange={(event) => form.setData('date_to', event.target.value)} />
                            <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" type="submit">{t('common.filter')}</button>
                        </div>
                    </form>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    {transfers.length === 0 ? (
                        <div className="p-6">
                            <EmptyState title={t('transfers.noTransfersFound')} description={t('transfers.employeeIdentityStable')} />
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-left text-sm">
                                <thead className="bg-gray-50 dark:bg-slate-950">
                                    <tr>
                                        {[t('employees.employeeNumber'), t('employees.fullName'), t('transfers.fromOrganization'), t('transfers.toOrganization'), t('transfers.effectiveDate'), t('common.status'), ''].map((heading) => (
                                            <th key={heading} className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">{heading}</th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {transfers.map((transfer) => (
                                        <tr key={transfer.id} className="border-t border-gray-100 text-gray-700 dark:border-slate-800 dark:text-slate-200">
                                            <td className="px-4 py-3 font-mono text-xs">{transfer.employee?.employee_number}</td>
                                            <td className="px-4 py-3">{transfer.employee?.full_name}</td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">{transfer.from_organization?.name_en ?? '—'}</td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">{transfer.to_organization?.name_en ?? '—'}</td>
                                            <td className="px-4 py-3">{transfer.effective_date ?? '—'}</td>
                                            <td className="px-4 py-3"><StatusBadge status={transfer.status} /></td>
                                            <td className="px-4 py-3 text-right">
                                                <Link href={route('employee-transfers.show', transfer.id)} className="text-xs font-medium text-blue-600 hover:text-blue-800">{t('common.view')}</Link>
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
