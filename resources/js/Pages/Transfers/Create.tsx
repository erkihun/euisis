import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useLocale } from '@/hooks/useLocale';

type EmployeeOption = {
    id: string;
    employee_number: string;
    full_name: string;
    status: string;
    current_assignment?: {
        organization_id: string;
        organization_name?: string | null;
        position_id?: string | null;
        position_name?: string | null;
    } | null;
};

export default function TransfersCreate({
    employees,
    organizations,
    positions,
    selectedEmployeeId,
}: {
    employees: EmployeeOption[];
    organizations: Array<{ id: string; name_en: string }>;
    positions: Array<{ id: string; job_position_code: string; title_en: string; organization_id: string | null }>;
    selectedEmployeeId?: string | null;
}) {
    const { t } = useLocale();
    const form = useForm({
        employee_id: selectedEmployeeId ?? employees[0]?.id ?? '',
        to_organization_id: '',
        to_position_id: '',
        effective_date: new Date().toISOString().slice(0, 10),
        transfer_reason: '',
    });
    const currentEmployee = useMemo(() => employees.find((employee) => employee.id === form.data.employee_id), [employees, form.data.employee_id]);
    const inputCls = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    function submit(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        form.post(route('employee-transfers.store'));
    }

    return (
        <AuthenticatedLayout header={<PageHeader title={t('transfers.createTransfer')} description={t('transfers.employeeIdentityStable')} />}>
            <Head title={t('transfers.createTransfer')} />
            <form className="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900" onSubmit={submit}>
                <div className="grid gap-4 md:grid-cols-2">
                    <select className={inputCls} value={form.data.employee_id} onChange={(event) => form.setData('employee_id', event.target.value)}>
                        <option value="">{t('transfers.selectEmployee')}</option>
                        {employees.map((employee) => <option key={employee.id} value={employee.id}>{employee.employee_number} · {employee.full_name}</option>)}
                    </select>
                    <input className={inputCls} value={currentEmployee?.current_assignment?.organization_name ?? ''} disabled placeholder={t('transfers.fromOrganization')} />
                    <select className={inputCls} value={form.data.to_organization_id} onChange={(event) => form.setData('to_organization_id', event.target.value)}>
                        <option value="">{t('transfers.selectOrganization')}</option>
                        {organizations.map((organization) => <option key={organization.id} value={organization.id}>{organization.name_en}</option>)}
                    </select>
                    <select className={inputCls} value={form.data.to_position_id} onChange={(event) => form.setData('to_position_id', event.target.value)}>
                        <option value="">{t('transfers.selectPosition')}</option>
                        {positions.map((position) => <option key={position.id} value={position.id}>{position.job_position_code} · {position.title_en}</option>)}
                    </select>
                    <input className={inputCls} type="date" value={form.data.effective_date} onChange={(event) => form.setData('effective_date', event.target.value)} />
                </div>
                <textarea className={`${inputCls} min-h-28`} value={form.data.transfer_reason} placeholder={t('transfers.transferReason')} onChange={(event) => form.setData('transfer_reason', event.target.value)} />
                <div className="flex gap-3">
                    <button className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" type="submit" disabled={form.processing}>{t('common.save')}</button>
                    <Link href={route('employee-transfers.index')} className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-slate-700 dark:text-slate-200">{t('common.cancel')}</Link>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
