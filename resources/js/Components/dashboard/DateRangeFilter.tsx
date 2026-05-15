import { router, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import { RefreshIcon } from '@/Components/Icons';

interface OrganizationOption {
    id: string;
    name: string;
    code: string;
}

interface Props {
    filters: {
        dateRange: string;
        dateFrom: string;
        dateTo: string;
        organizationId: string | null;
        organizationOptions: OrganizationOption[];
    };
    t: (key: string) => string;
}

export default function DateRangeFilter({ filters, t }: Props) {
    const form = useForm({
        date_range: filters.dateRange,
        date_from: filters.dateFrom,
        date_to: filters.dateTo,
        organization_id: filters.organizationId ?? '',
    });

    const submit = () => {
        router.get(route('dashboard'), form.data, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <div className="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex-row lg:items-end">
            <div className="grid flex-1 gap-3 md:grid-cols-4">
                <label className="text-sm">
                    <span className="mb-1 block text-gray-600 dark:text-slate-300">{t('dashboard.dateRange')}</span>
                    <select
                        value={form.data.date_range}
                        onChange={(event) => form.setData('date_range', event.target.value)}
                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    >
                        <option value="today">{t('dashboard.filters.today')}</option>
                        <option value="7d">{t('dashboard.filters.last7Days')}</option>
                        <option value="30d">{t('dashboard.filters.last30Days')}</option>
                        <option value="90d">{t('dashboard.filters.last90Days')}</option>
                        <option value="custom">{t('dashboard.filters.customRange')}</option>
                    </select>
                </label>

                <label className="text-sm">
                    <span className="mb-1 block text-gray-600 dark:text-slate-300">{t('common.effectiveFrom')}</span>
                    <input
                        type="date"
                        value={form.data.date_from}
                        onChange={(event) => form.setData('date_from', event.target.value)}
                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    />
                </label>

                <label className="text-sm">
                    <span className="mb-1 block text-gray-600 dark:text-slate-300">{t('common.effectiveTo')}</span>
                    <input
                        type="date"
                        value={form.data.date_to}
                        onChange={(event) => form.setData('date_to', event.target.value)}
                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    />
                </label>

                <label className="text-sm">
                    <span className="mb-1 block text-gray-600 dark:text-slate-300">{t('organizations.organization')}</span>
                    <select
                        value={form.data.organization_id}
                        onChange={(event) => form.setData('organization_id', event.target.value)}
                        className="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100"
                    >
                        <option value="">{t('dashboard.filters.allOrganizations')}</option>
                        {filters.organizationOptions.map((organization) => (
                            <option key={organization.id} value={organization.id}>
                                {organization.name}
                            </option>
                        ))}
                    </select>
                </label>
            </div>

            <div className="flex items-center gap-2">
                <Button type="button" onClick={submit}>
                    {t('dashboard.refresh')}
                </Button>
                <Button type="button" variant="outline" onClick={submit} icon={<RefreshIcon className="h-4 w-4" />}>
                    {t('common.filter')}
                </Button>
            </div>
        </div>
    );
}
