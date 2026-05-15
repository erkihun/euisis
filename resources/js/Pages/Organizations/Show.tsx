import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StatusBadge from '@/Components/StatusBadge';
import PageHeader from '@/Components/PageHeader';
import { PencilIcon, ArchiveIcon, Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

type NameHistory = {
    id: string;
    name_en: string;
    effective_from: string;
    effective_to?: string | null;
};

type Descendant = { descendant_organization_id: string; depth: number };

type CanProps = {
    update: boolean;
    archive: boolean;
    createChild: boolean;
};

export default function OrganizationShow({
    organization,
    currentAssignmentsCount,
    descendants,
    can,
}: {
    organization: {
        id: string;
        code: string;
        name_en: string;
        name_am?: string | null;
        status: string;
        legal_basis_ref?: string | null;
        type?: { name_en: string };
        merged_into?: { name_en: string } | null;
        name_histories: NameHistory[];
        logo_url: string | null;
        has_logo: boolean;
        branding_primary_color: string | null;
        branding_secondary_color: string | null;
    };
    currentAssignmentsCount: number;
    descendants: Descendant[];
    can: CanProps;
}) {
    const { t } = useLocale();
    const archiveForm = useForm({});

    function handleArchive() {
        if (!confirm(t('organizations.archiveConfirm'))) return;
        archiveForm.delete(route('organizations.archive', organization.id), {
            onSuccess: () => {},
        });
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={organization.name_en}
                    description={organization.code}
                    actions={
                        <div className="flex items-center gap-2">
                            {can.createChild && (
                                <Link
                                    href={route('organizations.create') + `?parent=${organization.id}`}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                                >
                                    <Plus className="h-3.5 w-3.5" />
                                    {t('organizations.addChild')}
                                </Link>
                            )}
                            {can.update && (
                                <Link
                                    href={route('organizations.edit', organization.id)}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800"
                                >
                                    <PencilIcon className="h-3.5 w-3.5" />
                                    {t('common.edit')}
                                </Link>
                            )}
                            {can.archive && (
                                <button
                                    type="button"
                                    disabled={archiveForm.processing}
                                    onClick={handleArchive}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 disabled:opacity-60 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20"
                                >
                                    <ArchiveIcon className="h-3.5 w-3.5" />
                                    {t('common.archive')}
                                </button>
                            )}
                        </div>
                    }
                />
            }
        >
            <Head title={organization.name_en} />

            <div className="grid gap-6 lg:grid-cols-[1.2fr_1fr]">
                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex items-start justify-between gap-4">
                        <div>
                            <p className="text-xs font-medium text-gray-400 dark:text-slate-500">
                                {organization.code}
                            </p>
                            <h3 className="mt-1 text-2xl font-semibold text-gray-900 dark:text-slate-100">
                                {organization.name_en}
                            </h3>
                            {organization.name_am && (
                                <p className="mt-0.5 text-sm text-gray-500 dark:text-slate-400">
                                    {organization.name_am}
                                </p>
                            )}
                        </div>
                        <StatusBadge status={organization.status} />
                    </div>

                    <dl className="mt-6 grid gap-4 sm:grid-cols-2">
                        {[
                            { label: t('organizations.type'), value: organization.type?.name_en ?? '—' },
                            { label: t('organizations.currentAssignments'), value: currentAssignmentsCount },
                            { label: t('organizations.mergedInto'), value: organization.merged_into?.name_en ?? '—' },
                            { label: t('organizations.legalBasis'), value: organization.legal_basis_ref ?? '—' },
                        ].map(({ label, value }) => (
                            <div key={label}>
                                <dt className="text-xs font-medium text-gray-500 dark:text-slate-400">
                                    {label}
                                </dt>
                                <dd className="mt-1 text-sm text-gray-800 dark:text-slate-200">
                                    {value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </section>

                <section className="rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="font-semibold text-gray-900 dark:text-slate-100">
                        {t('organizations.subtreeReach')}
                    </h3>
                    {descendants.length === 0 ? (
                        <p className="mt-4 text-sm text-gray-400 dark:text-slate-500">
                            {t('organizations.noDescendants')}
                        </p>
                    ) : (
                        <ul className="mt-4 space-y-1.5 text-sm text-gray-600 dark:text-slate-300">
                            {descendants.map((d) => (
                                <li
                                    key={d.descendant_organization_id}
                                    className="flex items-center justify-between"
                                >
                                    <span className="font-mono text-xs text-gray-500 dark:text-slate-400">
                                        {d.descendant_organization_id}
                                    </span>
                                    <span className="text-xs text-gray-400 dark:text-slate-500">
                                        {t('common.depth')} {d.depth}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>

            {(organization.has_logo || organization.branding_primary_color || organization.branding_secondary_color) && (
                <section className="mt-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <h3 className="font-semibold text-gray-900 dark:text-slate-100">
                        {t('organizations.branding')}
                    </h3>
                    <div className="mt-4 flex flex-wrap items-start gap-6">
                        {organization.has_logo && organization.logo_url && (
                            <div>
                                <p className="mb-1.5 text-xs font-medium text-gray-500 dark:text-slate-400">
                                    {t('organizations.logo')}
                                </p>
                                <img
                                    src={organization.logo_url}
                                    alt={`${organization.name_en} logo`}
                                    className="h-16 w-16 rounded-xl border border-gray-200 object-contain p-1.5 dark:border-slate-700"
                                />
                            </div>
                        )}
                        {(organization.branding_primary_color || organization.branding_secondary_color) && (
                            <div>
                                <p className="mb-1.5 text-xs font-medium text-gray-500 dark:text-slate-400">
                                    {t('organizations.colorPreview')}
                                </p>
                                <div className="flex items-center gap-3">
                                    {organization.branding_primary_color && (
                                        <div className="flex items-center gap-1.5">
                                            <span
                                                className="h-6 w-6 rounded-full border border-white shadow"
                                                style={{ backgroundColor: organization.branding_primary_color }}
                                            />
                                            <span className="font-mono text-xs text-gray-600 dark:text-slate-300">
                                                {organization.branding_primary_color}
                                            </span>
                                        </div>
                                    )}
                                    {organization.branding_secondary_color && (
                                        <div className="flex items-center gap-1.5">
                                            <span
                                                className="h-6 w-6 rounded-full border border-white shadow"
                                                style={{ backgroundColor: organization.branding_secondary_color }}
                                            />
                                            <span className="font-mono text-xs text-gray-600 dark:text-slate-300">
                                                {organization.branding_secondary_color}
                                            </span>
                                        </div>
                                    )}
                                </div>
                                {organization.branding_primary_color && (
                                    <div
                                        className="mt-3 rounded-lg border border-l-4 border-gray-200 px-3 py-2 text-xs text-gray-500 dark:border-slate-700 dark:text-slate-400"
                                        style={{ borderLeftColor: organization.branding_primary_color }}
                                    >
                                        {t('organizations.brandingPreview')}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </section>
            )}

            <section className="mt-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h3 className="font-semibold text-gray-900 dark:text-slate-100">{t('organizations.nameHistory')}</h3>
                <div className="mt-4 overflow-hidden rounded-xl border border-gray-100 dark:border-slate-800">
                    <table className="min-w-full text-left text-sm">
                        <thead className="bg-gray-50 dark:bg-slate-950">
                            <tr>
                                {[t('common.name'), t('common.effectiveFrom'), t('common.effectiveTo')].map((h) => (
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
                            {organization.name_histories.map((history) => (
                                <tr
                                    key={history.id}
                                    className="border-t border-gray-100 text-gray-700 dark:border-slate-800 dark:text-slate-200"
                                >
                                    <td className="px-4 py-3">{history.name_en}</td>
                                    <td className="px-4 py-3">{history.effective_from}</td>
                                    <td className="px-4 py-3">
                                        {history.effective_to ?? (
                                            <span className="text-gray-400 dark:text-slate-500">
                                                {t('common.current')}
                                            </span>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </section>
        </AuthenticatedLayout>
    );
}
