import { Link } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import OrganizationActionsMenu from '@/Components/OrganizationActionsMenu';
import { ChevronDown, ChevronRight } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

export type OrgTreeNode = {
    id: string;
    code: string;
    name_en: string;
    name_am: string | null;
    status: string;
    effective_from: string | null;
    effective_to: string | null;
    depth: number;
    parent_id: string | null;
    children_count: number;
    type: { name_en: string; name_am?: string | null; code: string } | null;
    branding_primary_color: string | null;
    logo_url: string | null;
    can?: {
        createChild: boolean;
    };
};

type Props = {
    node: OrgTreeNode;
    expanded: boolean;
    onToggle: (id: string) => void;
    can: {
        update: boolean;
        archive: boolean;
        createChild: boolean;
    };
};

const INDENT_PX = 20;

export default function OrganizationTreeRow({ node, expanded, onToggle, can }: Props) {
    const { locale } = useLocale();
    const indent = node.depth * INDENT_PX;
    const hasChildren = node.children_count > 0;
    const organizationName = locale === 'am' && node.name_am ? node.name_am : node.name_en;
    const typeName = locale === 'am' && node.type?.name_am ? node.type.name_am : node.type?.name_en;

    return (
        <tr className="group border-t border-gray-100 text-sm text-gray-700 dark:border-slate-800 dark:text-slate-200">
            <td className="py-2.5 pl-4 pr-2" style={{ paddingLeft: `${16 + indent}px` }}>
                <div className="flex items-center gap-1.5">
                    {hasChildren ? (
                        <button
                            type="button"
                            onClick={() => onToggle(node.id)}
                            aria-expanded={expanded}
                            aria-label={expanded ? 'Collapse organization' : 'Expand organization'}
                            className="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-200 text-gray-500 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-slate-700 dark:text-slate-300 dark:hover:border-blue-500 dark:hover:bg-slate-800 dark:hover:text-blue-300"
                        >
                            {expanded ? (
                                <ChevronDown className="h-3.5 w-3.5" />
                            ) : (
                                <ChevronRight className="h-3.5 w-3.5" />
                            )}
                        </button>
                    ) : (
                        <span className="inline-flex h-6 w-6 shrink-0 items-center justify-center">
                            {node.depth > 0 && (
                                <span className="h-1.5 w-1.5 rounded-full bg-gray-300 dark:bg-slate-600" />
                            )}
                        </span>
                    )}
                    <Link
                        href={route('organizations.show', node.id)}
                        className="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        {node.code}
                    </Link>
                </div>
            </td>
            <td className="px-3 py-2.5">
                <div className="flex items-center gap-1.5">
                    {node.branding_primary_color && (
                        <span
                            className="h-2.5 w-2.5 shrink-0 rounded-full"
                            style={{ backgroundColor: node.branding_primary_color }}
                            title={node.branding_primary_color}
                        />
                    )}
                    <span className="text-gray-800 dark:text-slate-100">{organizationName}</span>
                    {node.children_count > 0 && (
                        <span className="text-xs text-gray-400 dark:text-slate-500">
                            {node.children_count} sub
                        </span>
                    )}
                </div>
            </td>
            <td className="px-3 py-2.5 text-xs text-gray-500 dark:text-slate-400">
                {typeName ?? '-'}
            </td>
            <td className="px-3 py-2.5">
                <StatusBadge status={node.status} />
            </td>
            <td className="px-3 py-2.5 text-xs text-gray-400 dark:text-slate-500">
                {node.effective_from ?? '-'}
            </td>
            <td className="py-2.5 pl-3 pr-4 text-right">
                <OrganizationActionsMenu organizationId={node.id} can={can} />
            </td>
        </tr>
    );
}
