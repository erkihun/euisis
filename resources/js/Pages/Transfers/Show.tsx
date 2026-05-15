import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLocale } from '@/hooks/useLocale';

export default function TransfersShow({ transfer }: { transfer: any }) {
    const { t } = useLocale();
    const rejectionForm = useForm({ rejection_reason: '' });

    function reject(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        rejectionForm.post(route('employee-transfers.reject', transfer.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={transfer.employee ? `${transfer.employee.employee_number} · ${transfer.employee.full_name}` : t('transfers.transferDetails')}
                    description={t('transfers.transferTimeline')}
                    actions={
                        <div className="flex flex-wrap gap-2">
                            {transfer.can?.update && <Link href={route('employee-transfers.edit', transfer.id)} className="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200">{t('common.edit')}</Link>}
                            {transfer.can?.submit && <button type="button" onClick={() => router.post(route('employee-transfers.submit', transfer.id))} className="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700">{t('transfers.submitTransfer')}</button>}
                            {transfer.can?.confirmCurrentOrganization && <button type="button" onClick={() => router.post(route('employee-transfers.confirm-current-organization', transfer.id))} className="rounded-lg bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700">{t('transfers.confirmCurrentOrganization')}</button>}
                            {transfer.can?.confirmReceivingOrganization && <button type="button" onClick={() => router.post(route('employee-transfers.confirm-receiving-organization', transfer.id))} className="rounded-lg bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700">{t('transfers.confirmReceivingOrganization')}</button>}
                            {transfer.can?.approve && <button type="button" onClick={() => router.post(route('employee-transfers.approve', transfer.id))} className="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-emerald-700">{t('transfers.approveTransfer')}</button>}
                            {transfer.can?.cancel && <button type="button" onClick={() => router.post(route('employee-transfers.cancel', transfer.id))} className="rounded-lg bg-slate-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">{t('transfers.cancelTransfer')}</button>}
                        </div>
                    }
                />
            }
        >
            <Head title={t('transfers.transferDetails')} />
            <div className="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                <section className="space-y-6">
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <div className="grid gap-4 md:grid-cols-2 text-sm">
                            <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.transferStatus')}</div><div className="mt-1"><StatusBadge status={transfer.status} /></div></div>
                            <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.effectiveDate')}</div><div className="mt-1">{transfer.effective_date ?? '—'}</div></div>
                            <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.fromOrganization')}</div><div className="mt-1">{transfer.from_organization?.name_en ?? '—'}</div></div>
                            <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.toOrganization')}</div><div className="mt-1">{transfer.to_organization?.name_en ?? '—'}</div></div>
                            <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.fromPosition')}</div><div className="mt-1">{transfer.from_position?.job_position_code ? `${transfer.from_position.job_position_code} · ${transfer.from_position.title_en}` : '—'}</div></div>
                            <div><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.toPosition')}</div><div className="mt-1">{transfer.to_position?.job_position_code ? `${transfer.to_position.job_position_code} · ${transfer.to_position.title_en}` : '—'}</div></div>
                            <div className="md:col-span-2"><div className="text-xs text-gray-500 dark:text-slate-400">{t('transfers.transferReason')}</div><div className="mt-1">{transfer.transfer_reason ?? '—'}</div></div>
                        </div>
                    </div>
                    {transfer.can?.reject && (
                        <form className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900" onSubmit={reject}>
                            <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('transfers.rejectTransfer')}</h3>
                            <textarea className="mt-4 min-h-28 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" value={rejectionForm.data.rejection_reason} onChange={(event) => rejectionForm.setData('rejection_reason', event.target.value)} placeholder={t('transfers.rejectionReason')} />
                            <button className="mt-3 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700" type="submit">{t('transfers.rejectTransfer')}</button>
                        </form>
                    )}
                </section>
                <aside className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('transfers.transferTimeline')}</h3>
                    <div className="mt-4 space-y-3 text-sm">
                        {[
                            [t('transfers.statusSubmitted'), transfer.submitted_at ?? transfer.created_at],
                            [t('transfers.statusCurrentConfirmed'), transfer.current_org_confirmed_at],
                            [t('transfers.statusReceivingConfirmed'), transfer.receiving_org_confirmed_at],
                            [t('transfers.statusApproved'), transfer.approved_at],
                            [t('transfers.statusCompleted'), transfer.completed_at],
                            [t('transfers.statusRejected'), transfer.rejected_at],
                        ].map(([label, value]) => (
                            <div key={label} className="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                                <div className="font-medium text-gray-900 dark:text-slate-100">{label}</div>
                                <div className="text-gray-500 dark:text-slate-400">{value ?? '—'}</div>
                            </div>
                        ))}
                    </div>
                </aside>
            </div>
        </AuthenticatedLayout>
    );
}
