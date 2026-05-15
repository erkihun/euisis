import type { HierarchyTreeNodeData } from '@/Components/hierarchy/types';

export function collectExpandableIds(nodes: HierarchyTreeNodeData[]): string[] {
    if (!Array.isArray(nodes)) {
        return [];
    }

    return nodes.flatMap((node) => {
        const children = Array.isArray(node.children) ? node.children : [];

        return [
            ...(children.length > 0 ? [node.organization_id] : []),
            ...collectExpandableIds(children),
        ];
    });
}

export function collectExpandedIdsToDepth(
    nodes: HierarchyTreeNodeData[],
    maxExpandedDepth: number,
): string[] {
    if (!Array.isArray(nodes)) {
        return [];
    }

    return nodes.flatMap((node) => {
        const children = Array.isArray(node.children) ? node.children : [];
        const nodeDepth = typeof node.depth === 'number' ? node.depth : 0;
        const shouldExpandNode = children.length > 0 && nodeDepth <= maxExpandedDepth;

        return [
            ...(shouldExpandNode ? [node.organization_id] : []),
            ...collectExpandedIdsToDepth(children, maxExpandedDepth),
        ];
    });
}
