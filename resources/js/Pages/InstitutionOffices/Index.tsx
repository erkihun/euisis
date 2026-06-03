import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface InstitutionOption {
    id: string;
    name_en: string;
    name_am: string | null;
    code: string;
}

interface LevelOption {
    value: string;
    label: string;
}

interface OfficeItem {
    id: string;
    institution_id: string;
    institution: { id: string; name_en: string; code: string } | null;
    geographic_organization_id: string | null;
    geographicOrganization: { id: string; name_en: string; code: string } | null;
    parent_office_id: string | null;
    parentOffice: { id: string; name_en: string; office_code: string } | null;
    office_code: string;
    name_en: string | null;
    name_am: string | null;
    office_level: string;
    status: string;
    is_head_office: boolean;
    child_offices_count: number;
}

interface Props {
    offices: PaginatedResponse<OfficeItem>;
    institutions: InstitutionOption[];
    levelOptions: LevelOption[];
    statusOptions: LevelOption[];
    filters: {
        institution_id?: string;
        office_level?: string;
        status?: string;
        search?: string;
    };
    can: { create: boolean };
}

export default function InstitutionOfficesIndex({
    offices,
    institutions,
    levelOptions,
    statusOptions,
    filters,
    can,
}: Props) {
    const { t } = useLocale();

    const inputCls =
        'rounded-md border border-slate-300 px-3 py-1.5 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100';

    function handleFilter(key: string, value: string) {
        router.get(
            route('institution-offices.index'),
            { ...filters, [key]: value || undefined },
            { preserveState: true, preserveScroll: true },
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('institutionOffices.title')}
                    actions={
                        can.create ? (
                            <Link
                                href={route('institution-offices.create')}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                <Plus className="h-4 w-4" />
                                {t('institutionOffices.create')}
                            </Link>
                        ) : undefined
                    }
                />
            }
        >
            <Head title={t('institutionOffices.title')} />

            {/* Filters */}
            <section className="mb-4 flex flex-wrap items-center gap-3">
                <input
                    type="text"
                    placeholder={t('institutionOffices.searchOffices')}
                    defaultValue={filters.search ?? ''}
                    onBlur={(e) => handleFilter('search', e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter') handleFilter('search', (e.target as HTMLInputElement).value);
                    }}
                    className={inputCls}
                />

                <select
                    value={filters.institution_id ?? ''}
                    onChange={(e) => handleFilter('institution_id', e.target.value)}
                    className={inputCls}
                >
                    <option value="">{t('institutionOffices.filterByInstitution')}</option>
                    {institutions.map((inst) => (
                        <option key={inst.id} value={inst.id}>
                            {inst.name_en} ({inst.code})
                        </option>
                    ))}
                </select>

                <select
                    value={filters.office_level ?? ''}
                    onChange={(e) => handleFilter('office_level', e.target.value)}
                    className={inputCls}
                >
                    <option value="">{t('institutionOffices.filterByLevel')}</option>
                    {levelOptions.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>

                <select
                    value={filters.status ?? ''}
                    onChange={(e) => handleFilter('status', e.target.value)}
                    className={inputCls}
                >
                    <option value="">{t('institutionOffices.filterByStatus')}</option>
                    {statusOptions.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>
            </section>

            <section className="rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <div className="overflow-x-auto">
                    <table className="min-w-full text-left text-sm">
                        <thead className="bg-gray-50 dark:bg-slate-950">
                            <tr>
                                {[
                                    t('institutionOffices.officeCode'),
                                    t('institutionOffices.officeName'),
                                    t('institutionOffices.officeLevel'),
                                    t('institutionOffices.geographicArea'),
                                    t('institutionOffices.parentOffice'),
                                    t('institutionOffices.institution'),
                                    t('institutionOffices.status'),
                                    t('common.actions'),
                                ].map((h) => (
                                    <th
                                        key={h}
                                        className="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-slate-400"
                                    >
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {offices.data.length === 0 ? (
                                <tr>
                                    <td
                                        colSpan={8}
                                        className="px-4 py-8 text-center text-sm text-gray-400 dark:text-slate-500"
                                    >
                                        {t('institutionOffices.noOffices')}
                                    </td>
                                </tr>
                            ) : (
                                offices.data.map((office) => (
                                    <tr
                                        key={office.id}
                                        className="border-t border-gray-100 text-gray-700 dark:border-slate-800 dark:text-slate-200"
                                    >
                                        <td className="px-4 py-3 font-mono text-xs">{office.office_code}</td>
                                        <td className="px-4 py-3">
                                            <div className="font-medium">{office.name_en ?? '—'}</div>
                                            {office.name_am && (
                                                <div className="text-xs text-gray-500 dark:text-slate-400">
                                                    {office.name_am}
                                                </div>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                                {office.office_level}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">
                                            {office.geographicOrganization?.name_en ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">
                                            {office.parentOffice ? (
                                                <Link
                                                    href={route('institution-offices.show', office.parentOffice.id)}
                                                    className="text-blue-600 hover:underline dark:text-blue-400"
                                                >
                                                    {office.parentOffice.name_en}
                                                </Link>
                                            ) : (
                                                <span className="text-gray-400 dark:text-slate-500">—</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">
                                            {office.institution?.name_en ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <StatusBadge status={office.status} />
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex items-center gap-2">
                                                <Link
                                                    href={route('institution-offices.show', office.id)}
                                                    className="text-xs text-blue-600 hover:underline dark:text-blue-400"
                                                >
                                                    {t('common.view')}
                                                </Link>
                                                <Link
                                                    href={route('institution-offices.edit', office.id)}
                                                    className="text-xs text-gray-600 hover:underline dark:text-slate-300"
                                                >
                                                    {t('common.edit')}
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {offices.last_page > 1 && (
                    <div className="flex items-center justify-between border-t border-gray-100 px-4 py-3 dark:border-slate-800">
                        <p className="text-xs text-gray-500 dark:text-slate-400">
                            {offices.from}–{offices.to} / {offices.total}
                        </p>
                        <div className="flex gap-2">
                            {offices.prev_page_url && (
                                <Link
                                    href={offices.prev_page_url}
                                    className="rounded-md border border-gray-300 px-3 py-1 text-xs hover:bg-gray-50 dark:border-slate-600 dark:hover:bg-slate-800"
                                >
                                    {t('common.previous')}
                                </Link>
                            )}
                            {offices.next_page_url && (
                                <Link
                                    href={offices.next_page_url}
                                    className="rounded-md border border-gray-300 px-3 py-1 text-xs hover:bg-gray-50 dark:border-slate-600 dark:hover:bg-slate-800"
                                >
                                    {t('common.next')}
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </section>
        </AuthenticatedLayout>
    );
}
