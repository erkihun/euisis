export type HierarchyVersionPage = {
    id: string;
    version_name: string;
    status: string;
    effective_from: string | null;
    effective_to: string | null;
    notes?: string | null;
    source_document?: string | null;
    edges_count?: number;
};

export type OrganizationOption = {
    id: string;
    code: string;
    name_en: string;
    name_am: string | null;
    status: string;
    type: {
        code: string;
        name_en: string;
        name_am?: string | null;
    } | null;
};

export type HierarchyEdge = {
    id: string;
    hierarchy_version_id: string;
    parent_organization_id: string;
    child_organization_id: string;
    relationship_type: string;
    effective_from: string | null;
    effective_to: string | null;
    parent_organization: {
        id: string;
        code: string;
        name_en: string;
        name_am: string | null;
        status: string;
        logo_url: string | null;
        type: {
            code: string;
            name_en: string;
            name_am: string | null;
        } | null;
    } | null;
    child_organization: {
        id: string;
        code: string;
        name_en: string;
        name_am: string | null;
        status: string;
        logo_url: string | null;
        type: {
            code: string;
            name_en: string;
            name_am: string | null;
        } | null;
    } | null;
    can: {
        view: boolean;
        update: boolean;
        remove: boolean;
    };
};

export type HierarchyTreeNodeData = {
    organization_id: string;
    edge_id: string | null;
    parent_organization_id: string | null;
    code: string;
    name_en: string;
    name_am: string | null;
    organization_type: {
        code: string;
        name_en: string;
        name_am: string | null;
    } | null;
    status: string;
    logo_url: string | null;
    depth: number;
    child_count: number;
    relationship_type: string | null;
    effective_from: string | null;
    effective_to: string | null;
    can: {
        edit: boolean;
        remove: boolean;
        addChild: boolean;
    };
    children: HierarchyTreeNodeData[];
};
