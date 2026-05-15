import { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import OrganizationSelector from '@/Components/organization-units/OrganizationSelector';
import OrganizationUnitTree from '@/Components/organization-units/OrganizationUnitTree';
import { Plus, ChevronRight, Building2 } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';
import type { OrganizationSummary, OrganizationUnitTreeNode } from '@/types/organizationUnit';

interface Props {
    organizations: OrganizationSummary[];
    selectedOrganization: OrganizationSummary | null;
    organizationUnits: OrganizationUnitTreeNode[];
    can: { viewAny: boolean; create: boolean };
}

export default function OrganizationUnitsIndex({
    organizations,
    selectedOrganization,
    organizationUnits,
    can,
}: Props) {
    const { t } = useLocale();

    const [localSelected, setLocalSelected] = useState<OrganizationSummary | null>(
        selectedOrganization ?? null,
    );

    useEffect(() => {
        setLocalSelected(selectedOrganization ?? null);
    }, [selectedOrganization]);

    function selectOrganization(org: OrganizationSummary) {
        setLocalSelected(org);
        router.get(
            route('organization-units.index'),
            { organization_id: org.id },
            { preserveState: false, preserveScroll: false },
        );
    }

    function clearSelection() {
        setLocalSelected(null);
        router.get(route('organization-units.index'), {}, { preserveState: false });
    }

    const displayOrg = localSelected ?? selectedOrganization;

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('nav.organizationUnits')}
                    actions={
                        displayOrg && can.create ? (
                            <Link
                                href={route('organization-units.create', {
                                    organization_id: displayOrg.id,
                                })}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                <Plus className="h-3.5 w-3.5" />
                                {t('organizationUnits.addOrganizationUnit')}
                            </Link>
                        ) : undefined
                    }
                />
            }
        >
            <Head title={t('nav.organizationUnits')} />

            <div className="space-y-5">
                {/* Breadcrumb when an org is selected */}
                {displayOrg && (
                    <nav className="flex items-center gap-1.5 text-sm text-gray-500 dark:text-slate-400">
                        <button
                            type="button"
                            onClick={clearSelection}
                            className="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            {t('organizationUnits.organizationUnits')}
                        </button>
                        <ChevronRight className="h-3.5 w-3.5" />
                        <span className="font-medium text-gray-900 dark:text-slate-100">{displayOrg.name_en}</span>
                    </nav>
                )}

                {displayOrg ? (
                    <>
                        {/* Selected org summary card */}
                        <section className="rounded-2xl border border-gray-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                            <div className="flex flex-wrap items-center gap-4">
                                {displayOrg.has_logo && displayOrg.logo_url ? (
                                    <img
                                        src={displayOrg.logo_url}
                                        alt=""
                                        className="h-12 w-12 rounded-xl object-cover"
                                    />
                                ) : (
                                    <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-lg font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {displayOrg.name_en.charAt(0).toUpperCase()}
                                    </span>
                                )}
                                <div className="flex-1 min-w-0">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <h2 className="text-base font-semibold text-gray-900 dark:text-slate-100">
                                            {displayOrg.name_en}
                                        </h2>
                                        {displayOrg.name_am && (
                                            <span className="text-sm text-gray-500 dark:text-slate-400">
                                                {displayOrg.name_am}
                                            </span>
                                        )}
                                        <StatusBadge status={displayOrg.status} />
                                    </div>
                                    <div className="mt-1 flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-slate-400">
                                        <span className="font-mono">{displayOrg.code}</span>
                                        {displayOrg.type && (
                                            <span>{displayOrg.type.name_en}</span>
                                        )}
                                        <span>
                                            {displayOrg.organization_units_count ?? 0}{' '}
                                            {t('organizationUnits.unitCount').toLowerCase()}
                                        </span>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onClick={clearSelection}
                                    className="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                >
                                    {t('organizationUnits.backToOrganizations')}
                                </button>
                            </div>
                        </section>

                        {/* Unit tree */}
                        <section>
                            <OrganizationUnitTree
                                units={organizationUnits}
                                canCreate={can.create}
                                canUpdate={can.create}
                                canDelete={can.create}
                                canRestore={can.create}
                                selectedOrgId={displayOrg.id}
                            />
                        </section>
                    </>
                ) : (
                    <>
                        {organizations.length === 0 ? (
                            <EmptyState
                                title={t('organizationUnits.noOrganizationSelected')}
                                description={t('organizationUnits.selectOrganizationToViewUnits')}
                                icon={<Building2 className="h-6 w-6" />}
                            />
                        ) : (
                            <OrganizationSelector
                                organizations={organizations}
                                onSelect={selectOrganization}
                            />
                        )}
                    </>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
