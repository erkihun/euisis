import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import { AlertTriangle } from '@/Components/Icons';
import { Plus } from '@/Components/Icons';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLocale } from '@/hooks/useLocale';

type EmployeeRow = {
    id: string;
    employee_number: string;
    full_name: string;
    phone: string | null;
    email: string | null;
    status: string;
    duplicate_flags_count?: number;
    current_assignment?: {
        organization?: { name_en: string } | null;
        position?: { title_en: string } | null;
    } | null;
};

export default function EmployeesIndex({
    employees,
    filters,
    can,
}: {
    employees: EmployeeRow[];
    filters: { search?: string; status?: string };
    can: { create: boolean };
}) {
    const { t } = useLocale();

    const filterForm = useForm({
        search: filters.search ?? '',
        status: filters.status ?? '',
    });

    const submitFilters = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        router.get(route('employees.index'), filterForm.data, {
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
                    title={t('employees.title')}
                    description=""
                    actions={can.create ? (
                        <Link
                            href={route('employees.create')}
                            className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Plus className="h-3.5 w-3.5" />
                            {t('employees.createEmployee')}
                        </Link>
                    ) : undefined}
                />
            }
        >
            <Head title={t('employees.title')} />

            <div className="space-y-6">
                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <form
                        className="flex flex-col gap-3 sm:flex-row"
                        onSubmit={submitFilters}
                    >
                        <input
                            className={`${inputCls} flex-1`}
                            placeholder={t('employees.searchPlaceholder')}
                            value={filterForm.data.search}
                            onChange={(e) => filterForm.setData('search', e.target.value)}
                        />
                        <select
                            className={inputCls}
                            value={filterForm.data.status}
                            onChange={(e) => filterForm.setData('status', e.target.value)}
                        >
                            <option value="">{t('employees.allStatuses')}</option>
                            <option value="active">{t('employees.active')}</option>
                            <option value="suspended">{t('employees.suspended')}</option>
                            <option value="transferred">{t('employees.transferred')}</option>
                            <option value="retired">{t('employees.retired')}</option>
                        </select>
                        <button
                            type="submit"
                            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            {t('common.filter')}
                        </button>
                    </form>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    {employees.length === 0 ? (
                        <div className="p-6">
                            <EmptyState
                                title={t('employees.noEmployeesFound')}
                                description={t('employees.searchFiltersHint')}
                            />
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="min-w-full text-left text-sm">
                                <thead className="bg-gray-50 dark:bg-slate-950">
                                    <tr>
                                        {[
                                            t('employees.employeeNumber'),
                                            t('employees.columnName'),
                                            t('employees.columnStatus'),
                                            t('employees.columnOrganization'),
                                            t('employees.columnPosition'),
                                            t('employees.columnFlags'),
                                            '',
                                        ].map((h, idx) => (
                                            <th
                                                key={idx}
                                                className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400"
                                            >
                                                {h}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody>
                                    {employees.map((emp) => (
                                        <tr
                                            key={emp.id}
                                            className="border-t border-gray-100 text-gray-700 dark:border-slate-800 dark:text-slate-200"
                                        >
                                            <td className="px-4 py-3">
                                                <Link
                                                    href={route('employees.show', emp.id)}
                                                    className="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                >
                                                    {emp.employee_number}
                                                </Link>
                                            </td>
                                            <td className="px-4 py-3">{emp.full_name}</td>
                                            <td className="px-4 py-3">
                                                <StatusBadge status={emp.status} />
                                            </td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">
                                                {emp.current_assignment?.organization?.name_en ?? t('common.unassigned')}
                                            </td>
                                            <td className="px-4 py-3 text-gray-500 dark:text-slate-400">
                                                {emp.current_assignment?.position?.title_en ?? '—'}
                                            </td>
                                            <td className="px-4 py-3">
                                                {(emp.duplicate_flags_count ?? 0) > 0 ? (
                                                    <span className="inline-flex items-center gap-1 text-orange-600 dark:text-orange-400">
                                                        <AlertTriangle className="h-3.5 w-3.5" aria-hidden="true" />
                                                        {emp.duplicate_flags_count}
                                                    </span>
                                                ) : (
                                                    <span className="text-gray-400 dark:text-slate-500">—</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <div className="flex items-center justify-end gap-3">
                                                    <Link
                                                        href={route('employees.show', emp.id)}
                                                        className="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                                    >
                                                        {t('common.view')}
                                                    </Link>
                                                    <Link
                                                        href={route('employees.edit', emp.id)}
                                                        className="text-xs font-medium text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200"
                                                    >
                                                        {t('employees.editEmployee')}
                                                    </Link>
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
