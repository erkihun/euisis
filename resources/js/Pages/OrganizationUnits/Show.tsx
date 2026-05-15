import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import OrganizationUnitStatusBadge from '@/Components/organization-units/OrganizationUnitStatusBadge';
import OrganizationUnitTypeBadge from '@/Components/organization-units/OrganizationUnitTypeBadge';
import { Head, Link, useForm } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';
import type { OrganizationUnit } from '@/types/organizationUnit';

interface Props {
    unit: OrganizationUnit;
}

export default function OrganizationUnitsShow({ unit }: Props) {
    const { t } = useLocale();
    const { post, processing } = useForm();

    function handleArchive() {
        if (!confirm(t('common.cannotUndo'))) return;
        post(route('organization-units.archive', unit.id));
    }

    function handleRestore() {
        post(route('organization-units.restore', unit.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={unit.name_en}
                    actions={
                        <div className="flex gap-2">
                            {unit.can.update && (
                                <Link
                                    href={route('organization-units.edit', unit.id)}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                >
                                    {t('common.edit')}
                                </Link>
                            )}
                            {unit.can.archive && unit.status !== 'archived' && (
                                <button
                                    type="button"
                                    onClick={handleArchive}
                                    disabled={processing}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-slate-900 dark:text-red-400"
                                >
                                    {t('common.delete')}
                                </button>
                            )}
                            {unit.can.restore && unit.status === 'archived' && (
                                <button
                                    type="button"
                                    onClick={handleRestore}
                                    disabled={processing}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:border-emerald-800 dark:bg-slate-900 dark:text-emerald-400"
                                >
                                    {t('common.restore')}
                                </button>
                            )}
                        </div>
                    }
                />
            }
        >
            <Head title={unit.name_en} />

            <div className="mx-auto max-w-3xl space-y-6">
                {/* Header badges */}
                <div className="flex flex-wrap items-center gap-2">
                    <span className="font-mono text-sm text-gray-500 dark:text-slate-400">{unit.code}</span>
                    <OrganizationUnitTypeBadge unitType={unit.unit_type} />
                    <OrganizationUnitStatusBadge status={unit.status} />
                </div>

                {/* Details Card */}
                <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h2 className="mb-4 text-base font-semibold text-gray-900 dark:text-slate-100">
                        {t('organizationUnits.organizationUnitDetails')}
                    </h2>
                    <dl className="grid grid-cols-2 gap-x-8 gap-y-4 text-sm">
                        <div>
                            <dt className="text-gray-500 dark:text-slate-400">{t('organizationUnits.organization')}</dt>
                            <dd className="font-medium text-gray-900 dark:text-slate-100">
                                {unit.organization ? (
                                    <Link
                                        href={route('organizations.show', unit.organization.id)}
                                        className="text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {unit.organization.name_en}
                                    </Link>
                                ) : '—'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-gray-500 dark:text-slate-400">
                                {t('organizationUnits.parentUnit')}
                            </dt>
                            <dd className="font-medium text-gray-900 dark:text-slate-100">
                                {unit.parent ? (
                                    <Link
                                        href={route('organization-units.show', unit.parent.id)}
                                        className="text-blue-600 hover:underline dark:text-blue-400"
                                    >
                                        {unit.parent.name_en}
                                    </Link>
                                ) : '—'}
                            </dd>
                        </div>
                        {unit.effective_from && (
                            <div>
                                <dt className="text-gray-500 dark:text-slate-400">
                                    {t('organizationUnits.effectiveFrom')}
                                </dt>
                                <dd className="font-medium text-gray-900 dark:text-slate-100">
                                    {unit.effective_from}
                                </dd>
                            </div>
                        )}
                        {unit.effective_to && (
                            <div>
                                <dt className="text-gray-500 dark:text-slate-400">
                                    {t('organizationUnits.effectiveTo')}
                                </dt>
                                <dd className="font-medium text-gray-900 dark:text-slate-100">
                                    {unit.effective_to}
                                </dd>
                            </div>
                        )}
                        {unit.description_en && (
                            <div className="col-span-2">
                                <dt className="text-gray-500 dark:text-slate-400">
                                    {t('organizationUnits.descriptionEn')}
                                </dt>
                                <dd className="font-medium text-gray-900 dark:text-slate-100">
                                    {unit.description_en}
                                </dd>
                            </div>
                        )}
                    </dl>
                </section>

                {/* Child Units */}
                {(unit.children && unit.children.length > 0) && (
                    <section className="rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                        <h2 className="mb-4 text-base font-semibold text-gray-900 dark:text-slate-100">
                            {t('organizationUnits.childUnits')} ({unit.children.length})
                        </h2>
                        <ul className="divide-y divide-gray-100 dark:divide-slate-800">
                            {unit.children.map((child) => (
                                <li key={child.id} className="flex items-center justify-between py-2">
                                    <div>
                                        <Link
                                            href={route('organization-units.show', child.id)}
                                            className="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                                        >
                                            {child.name_en}
                                        </Link>
                                        <span className="ml-2 font-mono text-xs text-gray-400">{child.code}</span>
                                    </div>
                                    <div className="flex gap-2">
                                        <OrganizationUnitTypeBadge unitType={child.unit_type} />
                                        <OrganizationUnitStatusBadge status={child.status} />
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                <div className="flex gap-2">
                    <Link
                        href={route('organization-units.index')}
                        className="text-sm text-gray-500 hover:text-gray-700 dark:text-slate-400"
                    >
                        {t('organizationUnits.backToList')}
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
