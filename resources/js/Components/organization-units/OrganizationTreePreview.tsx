import { useState, useMemo } from 'react';
import { SearchIcon, Building2 } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';
import OrganizationTreePreviewNode from '@/Components/organization-units/OrganizationTreePreviewNode';
import type { OrganizationTreeNode } from '@/types/organizationUnit';

interface Props {
    tree: OrganizationTreeNode[];
    selectedId: string | null;
    hasPublishedHierarchy: boolean;
    onSelect: (node: OrganizationTreeNode) => void;
}

function collectAllIds(nodes: OrganizationTreeNode[]): string[] {
    const ids: string[] = [];
    for (const node of nodes) {
        ids.push(node.id);
        if (node.children.length > 0) {
            ids.push(...collectAllIds(node.children));
        }
    }
    return ids;
}

function matchesSearch(node: OrganizationTreeNode, q: string): boolean {
    return (
        node.name_en.toLowerCase().includes(q) ||
        (node.name_am ?? '').includes(q) ||
        node.code.toLowerCase().includes(q)
    );
}

function filterTree(nodes: OrganizationTreeNode[], q: string): OrganizationTreeNode[] {
    if (!q) return nodes;
    return nodes
        .map((node) => {
            const filteredChildren = filterTree(node.children, q);
            if (matchesSearch(node, q) || filteredChildren.length > 0) {
                return { ...node, children: filteredChildren };
            }
            return null;
        })
        .filter((n): n is OrganizationTreeNode => n !== null);
}

function collectMatchIds(nodes: OrganizationTreeNode[]): string[] {
    const ids: string[] = [];
    for (const node of nodes) {
        ids.push(node.id);
        if (node.children.length > 0) {
            ids.push(...collectMatchIds(node.children));
        }
    }
    return ids;
}

export default function OrganizationTreePreview({
    tree,
    selectedId,
    hasPublishedHierarchy,
    onSelect,
}: Props) {
    const { t } = useLocale();
    const [search, setSearch] = useState('');
    const [expandedIds, setExpandedIds] = useState<Set<string>>(() => {
        // Start with all root nodes (depth 0) expanded
        return new Set(tree.map((n) => n.id));
    });

    const allIds = useMemo(() => collectAllIds(tree), [tree]);

    const filteredTree = useMemo(() => {
        const q = search.trim().toLowerCase();
        return filterTree(tree, q);
    }, [tree, search]);

    // Auto-expand ancestors when searching
    const effectiveExpandedIds = useMemo(() => {
        if (!search.trim()) return expandedIds;
        // When searching, expand all nodes that contain matches
        return new Set(collectMatchIds(filteredTree));
    }, [search, filteredTree, expandedIds]);

    function toggleNode(id: string) {
        setExpandedIds((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    }

    function expandAll() {
        setExpandedIds(new Set(allIds));
    }

    function collapseAll() {
        setExpandedIds(new Set());
    }

    return (
        <div className="flex h-full flex-col rounded-2xl border border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            {/* Header */}
            <div className="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-slate-800">
                <h2 className="text-sm font-semibold text-gray-900 dark:text-slate-100">
                    {t('organizationUnits.organizationTreePreview')}
                </h2>
                <div className="flex items-center gap-1">
                    <button
                        type="button"
                        onClick={expandAll}
                        className="rounded px-2 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        {t('organizationUnits.expandAll')}
                    </button>
                    <button
                        type="button"
                        onClick={collapseAll}
                        className="rounded px-2 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        {t('organizationUnits.collapseAll')}
                    </button>
                </div>
            </div>

            {/* No published hierarchy warning */}
            {!hasPublishedHierarchy && (
                <div className="mx-3 mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                    {t('organizationUnits.noPublishedHierarchy')}
                </div>
            )}

            {/* Search */}
            <div className="px-3 py-2">
                <div className="relative">
                    <SearchIcon className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400 dark:text-slate-500" />
                    <input
                        type="text"
                        className="w-full rounded-lg border border-gray-300 bg-white py-1.5 pl-8 pr-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500"
                        placeholder={t('organizationUnits.searchOrganizations')}
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                </div>
            </div>

            {/* Tree */}
            <div className="flex-1 overflow-y-auto px-2 pb-3" role="tree">
                {filteredTree.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-8 text-center">
                        <Building2 className="h-8 w-8 text-gray-300 dark:text-slate-600" />
                        <p className="mt-2 text-xs text-gray-500 dark:text-slate-400">
                            {t('organizationUnits.noOrganizationsFound')}
                        </p>
                    </div>
                ) : (
                    filteredTree.map((node) => (
                        <OrganizationTreePreviewNode
                            key={node.id}
                            node={node}
                            expandedIds={effectiveExpandedIds}
                            selectedId={selectedId}
                            onToggle={toggleNode}
                            onSelect={onSelect}
                        />
                    ))
                )}
            </div>
        </div>
    );
}
