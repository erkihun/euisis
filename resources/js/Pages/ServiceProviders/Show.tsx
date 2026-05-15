import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import { Head } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';

type Provider = {
    id: string;
    name: string;
    code: string;
    status: string;
    service_type?: { name_en: string } | null;
    organization?: { name_en: string } | null;
};

type Transaction = {
    id: string;
    status: string;
    reference?: string | null;
    amount?: string | number | null;
    occurred_at?: string | null;
    service_type?: { name_en: string } | null;
};

export default function ServiceProvidersShow({
    provider,
    transactions,
}: {
    provider: Provider;
    transactions: Transaction[];
}) {
    const { t } = useLocale();

    return (
        <AuthenticatedLayout
            header={<PageHeader title={provider.name} description={provider.code} />}
        >
            <Head title={provider.name} />
            <div className="grid gap-6 xl:grid-cols-[1fr_1.5fr]">
                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="font-mono text-sm font-medium text-gray-900 dark:text-slate-100">
                                {provider.code}
                            </p>
                        </div>
                        <StatusBadge status={provider.status} />
                    </div>
                    <dl className="mt-4 space-y-3 text-sm">
                        <div>
                            <dt className="text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('providers.status')}
                            </dt>
                            <dd className="mt-0.5 text-gray-800 dark:text-slate-200">{provider.status}</dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('providers.serviceType')}
                            </dt>
                            <dd className="mt-0.5 text-gray-800 dark:text-slate-200">
                                {provider.service_type?.name_en ?? t('providers.unknown')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-xs font-medium text-gray-500 dark:text-slate-400">
                                {t('providers.organization')}
                            </dt>
                            <dd className="mt-0.5 text-gray-800 dark:text-slate-200">
                                {provider.organization?.name_en ?? t('providers.notLinked')}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="font-semibold text-gray-900 dark:text-slate-100">
                        {t('providers.recentProviderTransactions')}
                    </h3>
                    <div className="mt-4 space-y-3">
                        {transactions.length === 0 ? (
                            <EmptyState title={t('providers.noRecentTransactions')} />
                        ) : (
                            transactions.map((transaction) => (
                                <div
                                    key={transaction.id}
                                    className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="text-sm font-medium text-gray-800 dark:text-slate-200">
                                            {transaction.service_type?.name_en ?? t('providers.unknownService')}
                                        </p>
                                        <StatusBadge status={transaction.status} />
                                    </div>
                                    <p className="mt-1 text-xs text-gray-500 dark:text-slate-400">
                                        {transaction.reference ?? t('common.na')} ·{' '}
                                        {transaction.amount ?? t('common.na')}
                                    </p>
                                    {transaction.occurred_at && (
                                        <p className="mt-1 text-xs text-gray-400 dark:text-slate-500">
                                            {transaction.occurred_at}
                                        </p>
                                    )}
                                </div>
                            ))
                        )}
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
