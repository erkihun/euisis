import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState';
import CardStatusBadge from '@/Components/IdCards/CardStatusBadge';
import { Head, Link, router } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';
import { useState } from 'react';

type CardRow = {
    id: string;
    card_number: string;
    status: string;
    issued_at?: string | null;
    expires_at?: string | null;
    previous_card_id?: string | null;
    employee?: {
        full_name: string;
        employee_number: string;
        current_assignment?: { organization?: { name_en: string } | null } | null;
    } | null;
    can?: {
        view?: boolean;
        issue?: boolean;
        activate?: boolean;
        reportLost?: boolean;
        reportDamaged?: boolean;
        replace?: boolean;
        revoke?: boolean;
    };
};

type PageProps = {
    cards: {
        data: CardRow[];
        current_page: number;
        last_page: number;
        total: number;
    };
    can?: {
        submitRequest?: boolean;
        createPrintBatch?: boolean;
    };
    filters?: {
        search?: string;
        status?: string;
    };
};

const CARD_STATUSES = [
    'pending_print', 'printed', 'issued', 'active',
    'expired', 'lost', 'damaged', 'suspended', 'revoked', 'replaced',
];

export default function IdCardsIndex({ cards, can, filters = {} }: PageProps) {
    const { t } = useLocale();

    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');

    function applyFilters() {
        router.get(route('id-cards.index'), { search, status }, {
            preserveState: true,
            replace: true,
        });
    }

    function resetFilters() {
        setSearch(''); setStatus('');
        router.get(route('id-cards.index'), {}, { preserveState: false, replace: true });
    }

    const fmtDate = (v?: string | null) =>
        v ? new Date(v).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

    const selectCls = 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200';
    const inputCls  = 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:placeholder:text-slate-500';

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('idCards.title')}
                    description=""
                    actions={
                        <div className="flex gap-2">
                            {can?.submitRequest && (
                                <Link
                                    href={route('card-requests.create')}
                                    className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                                >
                                    {t('idCards.newRequest')}
                                </Link>
                            )}
                            {can?.createPrintBatch && (
                                <Link
                                    href={route('print-batches.create')}
                                    className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    {t('idCards.createPrintBatch')}
                                </Link>
                            )}
                            <Link
                                href={route('card-requests.index')}
                                className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
                            >
                                {t('idCards.smartButtons.requests')}
                            </Link>
                        </div>
                    }
                />
            }
        >
            <Head title={t('idCards.title')} />

            {/* Filter bar */}
            <div className="mb-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <div className="flex flex-wrap gap-3 items-end">
                    <div className="flex-1 min-w-[180px]">
                        <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-slate-400">
                            {t('common.search')}
                        </label>
                        <input
                            className={inputCls + ' w-full'}
                            placeholder={t('idCards.searchCards')}
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => { if (e.key === 'Enter') applyFilters(); }}
                        />
                    </div>
                    <div>
                        <label className="mb-1 block text-xs font-medium text-gray-500 dark:text-slate-400">
                            {t('common.status')}
                        </label>
                        <select className={selectCls} value={status} onChange={(e) => setStatus(e.target.value)}>
                            <option value="">{t('common.filter')}…</option>
                            {CARD_STATUSES.map((s) => (
                                <option key={s} value={s}>
                                    {t(`idCards.status_${s}`) || s}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex gap-2">
                        <button
                            type="button"
                            onClick={applyFilters}
                            className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                        >
                            {t('common.filter')}
                        </button>
                        <button
                            type="button"
                            onClick={resetFilters}
                            className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                        >
                            {t('common.reset')}
                        </button>
                    </div>
                </div>
            </div>

            <div className="rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                {cards.data.length === 0 ? (
                    <div className="p-8">
                        <EmptyState
                            title={t('idCards.noIdCardsFound')}
                            description={t('idCards.cardsAfterApproval')}
                            action={
                                can?.submitRequest ? (
                                    <Link
                                        href={route('card-requests.create')}
                                        className="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                                    >
                                        {t('idCards.newRequest')}
                                    </Link>
                                ) : undefined
                            }
                        />
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-800/50">
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                        {t('idCards.cardNumber')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                        {t('employees.employeeNumber')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                        {t('organizations.organization')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                        {t('common.status')}
                                    </th>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                        {t('idCards.expiryDate')}
                                    </th>
                                    <th className="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                                        {t('common.actions')}
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                {cards.data.map((card) => (
                                    <tr
                                        key={card.id}
                                        className="transition-colors hover:bg-gray-50 dark:hover:bg-slate-800/50"
                                    >
                                        <td className="px-4 py-3">
                                            <span className="font-mono text-sm font-semibold text-gray-900 dark:text-slate-100">
                                                {card.card_number}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <p className="font-mono text-sm font-medium text-gray-800 dark:text-slate-200">
                                                {card.employee?.employee_number}
                                            </p>
                                            <p className="text-xs text-gray-400 dark:text-slate-500">
                                                {card.employee?.full_name}
                                            </p>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600 dark:text-slate-400 max-w-[200px] truncate">
                                            {card.employee?.current_assignment?.organization?.name_en ?? t('idCards.unknownOrg')}
                                        </td>
                                        <td className="px-4 py-3">
                                            <CardStatusBadge status={card.status} />
                                        </td>
                                        <td className="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                                            {fmtDate(card.expires_at)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {card.can?.view !== false && (
                                                <Link
                                                    href={route('id-cards.show', card.id)}
                                                    className="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                                >
                                                    {t('common.view')}
                                                </Link>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {cards.last_page > 1 && (
                    <div className="flex items-center justify-between border-t border-gray-100 px-4 py-3 text-xs text-gray-400 dark:border-slate-800 dark:text-slate-500">
                        <span>
                            {t('common.page')} {cards.current_page} {t('common.of')} {cards.last_page}
                        </span>
                        <span>{cards.total} {t('common.total').toLowerCase()}</span>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
