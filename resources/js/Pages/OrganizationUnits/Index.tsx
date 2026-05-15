import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import OrganizationTreePreview from '@/Components/organization-units/OrganizationTreePreview';
import OrganizationUnitTree from '@/Components/organization-units/OrganizationUnitTree';
import { Building2 } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';
import type { OrganizationSummary, OrganizationTreeNode, OrganizationUnitTreeNode } from '@/types/organizationUnit';

interface Props {
    organizationTree: OrganizationTreeNode[];
    hasPublishedHierarchy: boolean;
    selectedOrganization: OrganizationSummary | null;
    organizationUnits: OrganizationUnitTreeNode[];
    can: { viewAny: boolean; create: boolean };
}

export default function OrganizationUnitsIndex({
    organizationTree,
    hasPublishedHierarchy,
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

    function selectOrganization(node: OrganizationTreeNode) {
        router.get(
            route('organization-units.index'),
            { organization_id: node.id },
            { preserveState: false, preserveScroll: false },
        );
    }

    const displayOrg = localSelected ?? selectedOrganization;

    return (
        <AuthenticatedLayout
            header={<PageHeader title={t('nav.organizationUnits')} />}
        >
            <Head title={t('nav.organizationUnits')} />

            {/* Two-panel layout: org tree left, unit tree right */}
            <div className="flex flex-col gap-5 lg:flex-row lg:items-stretch">
                {/* Left: Organization Tree Preview (40%) */}
                <div className="w-full lg:w-[38%] lg:min-h-[600px]">
                    <OrganizationTreePreview
                        tree={organizationTree}
                        selectedId={displayOrg?.id ?? null}
                        hasPublishedHierarchy={hasPublishedHierarchy}
                        onSelect={selectOrganization}
                    />
                </div>

                {/* Right: Organization Unit Tree (60%) */}
                <div className="w-full lg:flex-1">
                    {displayOrg ? (
                        <div className="space-y-4">
                            {/* Selected org header card */}
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
                                </div>
                            </section>

                            {/* Unit tree */}
                            <OrganizationUnitTree
                                units={organizationUnits}
                                canCreate={can.create}
                                canUpdate={can.create}
                                canDelete={can.create}
                                canRestore={can.create}
                                selectedOrgId={displayOrg.id}
                            />
                        </div>
                    ) : (
                        <div className="flex h-full min-h-[300px] flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900">
                            <Building2 className="h-10 w-10 text-gray-300 dark:text-slate-600" />
                            <p className="mt-3 text-sm font-medium text-gray-500 dark:text-slate-400">
                                {t('organizationUnits.selectOrganizationToViewUnits')}
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
