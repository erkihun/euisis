import { Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import StatusBadge from '@/Components/StatusBadge';
import EmptyState from '@/Components/EmptyState';
import PageHeader from '@/Components/PageHeader';
import OrganizationTreeRow, { OrgTreeNode } from '@/Components/OrganizationTreeRow';
import { Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

type HierarchyVersion = {
    id: string;
    version_name: string;
    status: string;
    approval_date: string | null;
};

type CanProps = {
    create: boolean;
    manageHierarchy: boolean;
};

type UnassignedOrg = {
    id: string;
    code: string;
    name_en: string;
    name_am: string | null;
    status: string;
    type?: { name_en: string; name_am?: string | null; code: string } | null;
};

export default function OrganizationsIndex({
    tree,
    unassigned,
    publishedVersion,
    hierarchyVersions,
    can,
}: {
    tree: OrgTreeNode[];
    unassigned: UnassignedOrg[];
    publishedVersion: { id: string; version_name: string; approval_date: string | null } | null;
    hierarchyVersions: HierarchyVersion[];
    can: CanProps;
}) {
    const { locale, t } = useLocale();
    const expandableIds = useMemo(
        () => tree.filter((node) => node.children_count > 0).map((node) => node.id),
        [tree],
    );
    const parentById = useMemo(
        () => new Map(tree.map((node) => [node.id, node.parent_id])),
        [tree],
    );
    const [expandedIds, setExpandedIds] = useState<Set<string>>(() => new Set(expandableIds));

    const visibleTree = useMemo(() => {
        return tree.filter((node) => {
            let parentId = node.parent_id;

            while (parentId !== null) {
                if (!expandedIds.has(parentId)) {
                    return false;
                }

                parentId = parentById.get(parentId) ?? null;
            }

            return true;
        });
    }, [expandedIds, parentById, tree]);

    function toggleNode(id: string) {
        setExpandedIds((current) => {
            const next = new Set(current);

            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }

            return next;
        });
    }

    function expandAll() {
        setExpandedIds(new Set(expandableIds));
    }

    function collapseAll() {
        setExpandedIds(new Set());
    }

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('organizations.title')}
                    description={
                        publishedVersion
                            ? `${t('organizations.hierarchyVersion')}: ${publishedVersion.version_name}`
                            : t('organizations.noHierarchy')
                    }
                />
            }
        >
            <Head title={t('organizations.title')} />

            {/* Hierarchy tree */}
            <section className="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div className="flex items-center justify-between px-5 py-4">
                    <div className="flex items-center gap-3">
                        <h3 className="font-semibold text-gray-900 dark:text-slate-100">
                            {t('organizations.registeredOrganizations')}
                        </h3>
                        <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-slate-800 dark:text-slate-400">
                            {tree.length}
                        </span>
                    </div>
                    <div className="flex flex-wrap items-center justify-end gap-2">
                        <button
                            type="button"
                            onClick={expandAll}
                            className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:border-blue-300 hover:text-blue-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:text-blue-300"
                        >
                            {t('organizations.expandAll')}
                        </button>
                        <button
                            type="button"
                            onClick={collapseAll}
                            className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:border-blue-300 hover:text-blue-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-blue-500 dark:hover:text-blue-300"
                        >
                            {t('organizations.collapseAll')}
                        </button>
                        {can.create && (
                            <Link
                                href={route('organizations.create')}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                            >
                                <Plus className="h-3.5 w-3.5" aria-hidden="true" />
                                {t('organizations.createOrganization')}
                            </Link>
                        )}
                    </div>
                </div>

                {tree.length === 0 ? (
                    <div className="px-5 pb-5">
                        <EmptyState
                            title={t('organizations.noOrganizations')}
                            description={
                                publishedVersion
                                    ? t('organizations.noHierarchy')
                                    : t('organizations.noHierarchy')
                            }
                        />
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-left text-sm">
                            <thead className="bg-gray-50 dark:bg-slate-950">
                                <tr>
                                    {[
                                        t('common.code'),
                                        t('common.name'),
                                        t('organizations.organizationType'),
                                        t('common.status'),
                                        t('common.effectiveFrom'),
                                        '',
                                    ].map((h) => (
                                        <th
                                            key={h}
                                            className="px-3 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 first:pl-5 last:pr-5 dark:text-slate-400"
                                        >
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {visibleTree.map((node) => (
                                    <OrganizationTreeRow
                                        key={node.id}
                                        node={node}
                                        expanded={expandedIds.has(node.id)}
                                        onToggle={toggleNode}
                                        can={{
                                            update: can.create,
                                            archive: can.create,
                                            createChild: node.can?.createChild ?? false,
                                        }}
                                    />
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>

            {/* Unassigned organizations */}
            {unassigned.length > 0 && (
                <section className="mt-4 rounded-xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex items-center justify-between px-5 py-4">
                        <div className="flex items-center gap-3">
                            <h3 className="font-semibold text-gray-900 dark:text-slate-100">
                                {t('organizations.noHierarchy')}
                            </h3>
                            <span className="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                                {unassigned.length}
                            </span>
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full text-left text-sm">
                            <thead className="bg-gray-50 dark:bg-slate-950">
                                <tr>
                                    {[
                                        t('common.code'),
                                        t('common.name'),
                                        t('organizations.organizationType'),
                                        t('common.status'),
                                    ].map((h) => (
                                        <th
                                            key={h}
                                            className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400"
                                        >
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {unassigned.map((org) => (
                                    <tr
                                        key={org.id}
                                        className="border-t border-gray-100 text-gray-700 dark:border-slate-800 dark:text-slate-200"
                                    >
                                        <td className="px-4 py-2.5">
                                            <Link
                                                href={route('organizations.show', org.id)}
                                                className="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                            >
                                                {org.code}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-2.5">{locale === 'am' && org.name_am ? org.name_am : org.name_en}</td>
                                        <td className="px-4 py-2.5 text-gray-500 dark:text-slate-400">
                                            {(locale === 'am' && org.type?.name_am ? org.type.name_am : org.type?.name_en) ?? '—'}
                                        </td>
                                        <td className="px-4 py-2.5">
                                            <StatusBadge status={org.status} />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            )}

            {/* Hierarchy versions */}
            {hierarchyVersions.length > 0 && (
                <section className="mt-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                    <div className="flex items-center justify-between gap-3">
                        <h3 className="font-semibold text-gray-900 dark:text-slate-100">
                            {t('organizations.hierarchyVersion')}
                        </h3>
                        <Link
                            href={route('hierarchy-versions.index')}
                            className="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium text-blue-600 hover:border-blue-300 hover:text-blue-700 dark:border-slate-700 dark:text-blue-400 dark:hover:border-blue-500 dark:hover:text-blue-300"
                        >
                            {t('organizations.viewAllHierarchyVersions')}
                        </Link>
                    </div>
                    <div className="mt-4 flex flex-wrap gap-3">
                        {hierarchyVersions.map((v) => (
                            <Link
                                key={v.id}
                                href={route('hierarchy-versions.show', { hierarchyVersion: v.id })}
                                className="flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:border-blue-400 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:text-blue-400"
                            >
                                <span className="font-medium">{v.version_name}</span>
                                <StatusBadge status={v.status} />
                            </Link>
                        ))}
                    </div>
                </section>
            )}
        </AuthenticatedLayout>
    );
}
