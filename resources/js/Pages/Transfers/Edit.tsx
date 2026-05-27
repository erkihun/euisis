import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLocale } from '@/hooks/useLocale';
import { toDateInput } from '@/lib/dateUtils';
import LocalizedDatePicker from '@/Components/Calendar/LocalizedDatePicker';

export default function TransfersEdit({ transfer, organizations, positions }: { transfer: any; organizations: Array<{ id: string; name_en: string }>; positions: Array<{ id: string; job_position_code: string; title_en: string; organization_id: string | null }> }) {
    const { t } = useLocale();
    const form = useForm({
        to_organization_id: transfer.to_organization?.id ?? '',
        to_position_id: transfer.to_position?.id ?? '',
        effective_date: toDateInput(transfer.effective_date),
        transfer_reason: transfer.transfer_reason ?? '',
    });
    const inputCls = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.patch(route('employee-transfers.update', transfer.id));
    }

    return (
        <AuthenticatedLayout header={<PageHeader title={t('transfers.editTransfer')} />}>
            <Head title={t('transfers.editTransfer')} />
            <form className="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" onSubmit={submit}>
                <div className="grid gap-4 md:grid-cols-2">
                    <input className={inputCls} value={transfer.employee ? `${transfer.employee.employee_number} · ${transfer.employee.full_name}` : ''} disabled />
                    <input className={inputCls} value={transfer.from_organization?.name_en ?? ''} disabled />
                    <select className={inputCls} value={form.data.to_organization_id} onChange={(event) => form.setData('to_organization_id', event.target.value)}>
                        <option value="">{t('transfers.selectOrganization')}</option>
                        {organizations.map((organization) => <option key={organization.id} value={organization.id}>{organization.name_en}</option>)}
                    </select>
                    <select className={inputCls} value={form.data.to_position_id} onChange={(event) => form.setData('to_position_id', event.target.value)}>
                        <option value="">{t('transfers.selectPosition')}</option>
                        {positions.map((position) => <option key={position.id} value={position.id}>{position.job_position_code} · {position.title_en}</option>)}
                    </select>
                    <LocalizedDatePicker className={inputCls} value={form.data.effective_date} onChange={(iso) => form.setData('effective_date', iso)} />
                </div>
                <textarea className={`${inputCls} min-h-28`} value={form.data.transfer_reason} placeholder={t('transfers.transferReason')} onChange={(event) => form.setData('transfer_reason', event.target.value)} />
                <div className="flex gap-3">
                    <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" type="submit" disabled={form.processing}>{t('common.save')}</button>
                    <Link href={route('employee-transfers.show', transfer.id)} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200">{t('common.cancel')}</Link>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
