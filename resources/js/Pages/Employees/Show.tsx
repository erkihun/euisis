import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import { Head, Link } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';

type EmployeeDetail = {
    id: string;
    employee_number: string;
    first_name: string;
    middle_name?: string | null;
    last_name: string;
    full_name: string;
    national_id?: string | null;
    phone?: string | null;
    email?: string | null;
    status: string;
    date_of_birth?: string | null;
    gender?: string | null;
    photo_url?: string | null;
    data_quality_score?: number | null;
    current_assignment?: {
        organization?: { name_en: string } | null;
        position?: { title_en: string } | null;
        effective_from?: string | null;
    } | null;
    assignments?: Array<{
        id: string;
        assignment_status: string;
        effective_from: string | null;
        effective_to: string | null;
        reason?: string | null;
        organization?: { id: string; name_en: string } | null;
        position?: { title_en: string } | null;
    }>;
    duplicate_flags?: Array<{
        id: string;
        risk_score: number;
        matched_fields: string[];
        matched_employee?: { employee_number: string; full_name: string } | null;
    }>;
    documents?: Array<{
        id: string;
        document_type: string;
        storage_disk: string;
        is_private: boolean;
        created_at: string | null;
    }>;
    transfers?: Array<{
        id: string;
        status: string;
        effective_date: string | null;
        from_organization?: { name_en: string } | null;
        to_organization?: { name_en: string } | null;
    }>;
};

function Field({ label, value }: { label: string; value?: string | number | null }) {
    return (
        <div>
            <dt className="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-slate-500">
                {label}
            </dt>
            <dd className="mt-1 text-sm text-gray-900 dark:text-slate-100">
                {value ?? <span className="text-gray-400 dark:text-slate-600">—</span>}
            </dd>
        </div>
    );
}

function formatNationalId(raw?: string | null): string {
    if (!raw) return '—';
    return raw.replace(/(.{4})/g, '$1 ').trim();
}

export default function EmployeesShow({ employee }: { employee: EmployeeDetail }) {
    const { t } = useLocale();

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    backHref={route('employees.index')}
                    title={employee.full_name}
                    description={employee.employee_number}
                    actions={
                        <Link
                            href={route('employees.edit', employee.id)}
                            className="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            {t('employees.editEmployee')}
                        </Link>
                    }
                />
            }
        >
            <Head title={employee.full_name} />

            <div className="space-y-6">

                {/* ── Profile card ─────────────────────────────────────── */}
                <div className="rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900 overflow-hidden">
                    {/* Blue header band */}
                    <div className="h-24 bg-gradient-to-r from-blue-600 to-blue-700" />

                    <div className="px-6 pb-6">
                        {/* Avatar row */}
                        <div className="flex flex-wrap items-end gap-4 -mt-12 mb-4">
                            <div className="flex-shrink-0">
                                {employee.photo_url ? (
                                    <img
                                        src={employee.photo_url}
                                        alt={employee.full_name}
                                        className="h-24 w-20 rounded-xl border-4 border-white object-cover shadow-md dark:border-slate-900"
                                    />
                                ) : (
                                    <div className="h-24 w-20 rounded-xl border-4 border-white bg-gradient-to-br from-blue-100 to-blue-200 dark:border-slate-900 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center shadow-md">
                                        <svg className="h-10 w-10 text-blue-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                )}
                            </div>
                            <div className="flex-1 min-w-0 pb-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <h2 className="text-xl font-bold text-gray-900 dark:text-slate-100 truncate">
                                        {employee.full_name}
                                    </h2>
                                    <StatusBadge status={employee.status} />
                                </div>
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-slate-400">
                                    {employee.current_assignment?.position?.title_en ?? t('employees.noPosition')}
                                    {employee.current_assignment?.organization?.name_en && (
                                        <> · {employee.current_assignment.organization.name_en}</>
                                    )}
                                </p>
                            </div>
                        </div>

                        {/* Identity details grid */}
                        <dl className="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                            <Field label={t('employees.employeeNumber')} value={employee.employee_number} />
                            <Field label={t('employees.nationalId')} value={formatNationalId(employee.national_id)} />
                            <Field label={t('common.status')} value={employee.status} />
                            <Field label={t('employees.firstName')} value={employee.first_name} />
                            <Field label={t('employees.middleName')} value={employee.middle_name} />
                            <Field label={t('employees.lastName')} value={employee.last_name} />
                            <Field label={t('employees.phone')} value={employee.phone} />
                            <Field label={t('employees.email')} value={employee.email} />
                            <Field label={t('employees.gender')} value={employee.gender} />
                            <div>
                                <dt className="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-slate-500">{t('employees.dateOfBirth')}</dt>
                                <dd className="mt-1 text-sm text-gray-900 dark:text-slate-100"><LocalizedDateDisplay value={employee.date_of_birth} /></dd>
                            </div>
                            <Field label={t('employees.dataQualityScore')} value={employee.data_quality_score ?? 0} />
                        </dl>
                    </div>
                </div>

                {/* ── Current assignment ───────────────────────────────── */}
                <div className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500 mb-4">
                        {t('employees.currentOrganization')}
                    </h3>
                    {employee.current_assignment ? (
                        <dl className="grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
                            <Field
                                label={t('organizations.title')}
                                value={employee.current_assignment.organization?.name_en}
                            />
                            <Field
                                label={t('employees.columnPosition')}
                                value={employee.current_assignment.position?.title_en}
                            />
                            <div>
                                <dt className="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-slate-500">{t('common.effectiveFrom')}</dt>
                                <dd className="mt-1 text-sm text-gray-900 dark:text-slate-100"><LocalizedDateDisplay value={employee.current_assignment.effective_from} /></dd>
                            </div>
                        </dl>
                    ) : (
                        <p className="text-sm text-gray-400 dark:text-slate-500">{t('common.unassigned')}</p>
                    )}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* ── Assignment history ───────────────────────────── */}
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500 mb-4">
                            {t('employees.assignmentHistory')}
                        </h3>
                        <div className="space-y-3 text-sm">
                            {(employee.assignments ?? []).length === 0 ? (
                                <p className="text-gray-400 dark:text-slate-500">—</p>
                            ) : (employee.assignments ?? []).map((a) => (
                                <div
                                    key={a.id}
                                    className="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="font-medium text-gray-900 dark:text-slate-100">
                                            {a.organization?.name_en ?? t('employees.unknownOrganization')}
                                        </div>
                                        <StatusBadge status={a.assignment_status} />
                                    </div>
                                    <div className="mt-1 text-gray-500 dark:text-slate-400">
                                        {a.position?.title_en ?? t('employees.noPosition')}
                                    </div>
                                    <div className="mt-1 text-xs text-gray-400 dark:text-slate-500">
                                        <LocalizedDateDisplay value={a.effective_from} fallback="?" />
                                        {' → '}
                                        {a.effective_to ? <LocalizedDateDisplay value={a.effective_to} /> : t('common.present')}
                                    </div>
                                    {a.reason && (
                                        <div className="mt-2 text-xs text-gray-400 dark:text-slate-500 italic">
                                            {a.reason}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* ── Transfers ────────────────────────────────────── */}
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <div className="flex items-center justify-between gap-3 mb-4">
                            <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500">
                                {t('employees.transferHistory')}
                            </h3>
                            <Link
                                href={route('transfers.dashboard')}
                                className="rounded-lg bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-700"
                            >
                                {t('nav.transferManagement')}
                            </Link>
                        </div>
                        <div className="space-y-3 text-sm">
                            {(employee.transfers ?? []).length === 0 ? (
                                <p className="text-gray-400 dark:text-slate-500">{t('transfers.noTransfersFound')}</p>
                            ) : (employee.transfers ?? []).map((tr) => (
                                <Link
                                    key={tr.id}
                                    href={route('transfers.dashboard')}
                                    className="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 p-4 transition hover:border-blue-200 hover:bg-blue-50/50 dark:border-slate-800 dark:bg-slate-950 dark:hover:border-blue-500/30"
                                >
                                    <div className="min-w-0">
                                        <div className="font-medium text-gray-900 dark:text-slate-100 truncate">
                                            {tr.from_organization?.name_en ?? '?'} → {tr.to_organization?.name_en ?? '?'}
                                        </div>
                                        <div className="mt-0.5 text-xs text-gray-400 dark:text-slate-500">
                                            {tr.effective_date ?? '—'}
                                        </div>
                                    </div>
                                    <StatusBadge status={tr.status} />
                                </Link>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* ── Duplicate warnings ───────────────────────────── */}
                    {(employee.duplicate_flags?.length ?? 0) > 0 && (
                        <div className="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/5">
                            <h3 className="text-sm font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400 mb-4">
                                {t('employees.duplicateWarnings')} ({employee.duplicate_flags?.length})
                            </h3>
                            <div className="space-y-3 text-sm">
                                {(employee.duplicate_flags ?? []).map((flag) => (
                                    <div
                                        key={flag.id}
                                        className="rounded-xl border border-amber-200 bg-white p-4 dark:border-amber-500/20 dark:bg-slate-900"
                                    >
                                        <div className="flex items-center justify-between gap-2">
                                            <span className="font-medium text-amber-700 dark:text-amber-300">
                                                {flag.matched_employee?.full_name ?? '—'}
                                            </span>
                                            <span className="text-xs font-mono text-amber-600 dark:text-amber-400">
                                                {t('employees.riskScore')} {flag.risk_score}
                                            </span>
                                        </div>
                                        <div className="mt-1 text-xs text-gray-500 dark:text-slate-400">
                                            #{flag.matched_employee?.employee_number} · {t('employees.matchedFields')}: {flag.matched_fields.join(', ') || '—'}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* ── Documents ────────────────────────────────────── */}
                    <div className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                        <h3 className="text-sm font-semibold uppercase tracking-wide text-gray-400 dark:text-slate-500 mb-4">
                            {t('employees.documentMetadata')}
                        </h3>
                        <div className="space-y-2 text-sm">
                            {(employee.documents ?? []).length === 0 ? (
                                <p className="text-gray-400 dark:text-slate-500">{t('employees.noDocuments')}</p>
                            ) : (employee.documents ?? []).map((doc) => (
                                <div
                                    key={doc.id}
                                    className="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950"
                                >
                                    <div className="min-w-0">
                                        <span className="font-medium text-gray-800 dark:text-slate-200">{doc.document_type}</span>
                                        <span className="mx-2 text-gray-300 dark:text-slate-600">·</span>
                                        <span className="font-mono text-xs text-gray-500 dark:text-slate-400">{doc.storage_disk}</span>
                                    </div>
                                    <span className={`text-xs font-medium ${doc.is_private ? 'text-red-500' : 'text-green-600'}`}>
                                        {doc.is_private ? t('common.private') : t('common.public')}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
