import { Link } from '@inertiajs/react';
import StatusBadge from '@/Components/StatusBadge';
import OrganizationActionsMenu from '@/Components/OrganizationActionsMenu';
import { ChevronRight } from '@/Components/Icons';

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
    type: { name_en: string; code: string } | null;
    branding_primary_color: string | null;
    logo_url: string | null;
    can?: {
        createChild: boolean;
    };
};

type Props = {
    node: OrgTreeNode;
    can: {
        update: boolean;
        archive: boolean;
        createChild: boolean;
    };
};

const INDENT_PX = 20;

export default function OrganizationTreeRow({ node, can }: Props) {
    const indent = node.depth * INDENT_PX;

    return (
        <tr className="group border-t border-gray-100 text-sm text-gray-700 dark:border-slate-800 dark:text-slate-200">
            <td className="py-2.5 pl-4 pr-2" style={{ paddingLeft: `${16 + indent}px` }}>
                <div className="flex items-center gap-1.5">
                    {node.depth > 0 && (
                        <ChevronRight className="h-3 w-3 shrink-0 text-gray-300 dark:text-slate-600" />
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
                    <span className="text-gray-800 dark:text-slate-100">{node.name_en}</span>
                    {node.children_count > 0 && (
                        <span className="text-xs text-gray-400 dark:text-slate-500">
                            {node.children_count} sub
                        </span>
                    )}
                </div>
            </td>
            <td className="px-3 py-2.5 text-xs text-gray-500 dark:text-slate-400">
                {node.type?.name_en ?? '-'}
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
