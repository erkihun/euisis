import { ChevronRight, ChevronDown, Building2 } from '@/Components/Icons';
import StatusBadge from '@/Components/StatusBadge';
import { useLocale } from '@/hooks/useLocale';
import type { OrganizationTreeNode } from '@/types/organizationUnit';

interface Props {
    node: OrganizationTreeNode;
    expandedIds: Set<string>;
    selectedId: string | null;
    onToggle: (id: string) => void;
    onSelect: (node: OrganizationTreeNode) => void;
}

export default function OrganizationTreePreviewNode({
    node,
    expandedIds,
    selectedId,
    onToggle,
    onSelect,
}: Props) {
    const { t } = useLocale();
    const indentPx = node.depth * 20;
    const hasChildren = node.children && node.children.length > 0;
    const isExpanded = expandedIds.has(node.id);
    const isSelected = selectedId === node.id;

    return (
        <div
            role="treeitem"
            aria-expanded={hasChildren ? isExpanded : undefined}
            aria-selected={isSelected}
        >
            <div
                className={`flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 transition-colors ${
                    isSelected
                        ? 'bg-blue-100 ring-2 ring-blue-400 dark:bg-blue-900/30 dark:ring-blue-500'
                        : 'hover:bg-gray-50 dark:hover:bg-slate-800/40'
                }`}
                style={{ paddingLeft: `${indentPx + 8}px` }}
                onClick={() => onSelect(node)}
            >
                {/* Expand/collapse toggle */}
                <button
                    type="button"
                    className="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded text-gray-400 hover:text-gray-600 dark:text-slate-500 dark:hover:text-slate-300"
                    onClick={(e) => {
                        e.stopPropagation();
                        if (hasChildren) onToggle(node.id);
                    }}
                    aria-label={
                        isExpanded
                            ? t('organizationUnits.collapseOrganization')
                            : t('organizationUnits.expandOrganization')
                    }
                    tabIndex={-1}
                >
                    {hasChildren ? (
                        isExpanded ? (
                            <ChevronDown className="h-3.5 w-3.5" />
                        ) : (
                            <ChevronRight className="h-3.5 w-3.5" />
                        )
                    ) : (
                        <span className="h-3.5 w-3.5" />
                    )}
                </button>

                {/* Logo / avatar */}
                {node.has_logo && node.logo_url ? (
                    <img
                        src={node.logo_url}
                        alt=""
                        className="h-7 w-7 flex-shrink-0 rounded-full object-cover"
                    />
                ) : (
                    <span className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        <Building2 className="h-3.5 w-3.5" />
                    </span>
                )}

                {/* Name + meta */}
                <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-1.5">
                        <span
                            className={`truncate text-sm font-medium ${
                                isSelected
                                    ? 'text-blue-800 dark:text-blue-200'
                                    : 'text-gray-900 dark:text-slate-100'
                            }`}
                        >
                            {node.name_en}
                        </span>
                        {node.type && (
                            <span className="inline-flex items-center rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {node.type.name_en}
                            </span>
                        )}
                        <StatusBadge status={node.status} />
                    </div>
                    <div className="flex items-center gap-2 text-[11px] text-gray-400 dark:text-slate-500">
                        <span className="font-mono">{node.code}</span>
                        {node.organization_units_count !== undefined && (
                            <span>
                                {node.organization_units_count} {t('organizationUnits.unitCount').toLowerCase()}
                            </span>
                        )}
                        {hasChildren && (
                            <span>
                                {node.children.length}{' '}
                                {t('organizationUnits.childOrganizations').toLowerCase()}
                            </span>
                        )}
                    </div>
                </div>
            </div>

            {/* Render children when expanded */}
            {hasChildren && isExpanded && (
                <div role="group">
                    {node.children.map((child) => (
                        <OrganizationTreePreviewNode
                            key={child.id}
                            node={child}
                            expandedIds={expandedIds}
                            selectedId={selectedId}
                            onToggle={onToggle}
                            onSelect={onSelect}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
