import { Link } from '@inertiajs/react';
import {
    LayoutDashboard,
    Building2,
    Users,
    CreditCard,
    Layers,
    ScrollText,
    X,
    SettingsIcon,
    ShieldCheck,
    Briefcase,
    TrashIcon,
    // new distinct icons
    TagsIcon,
    GitBranchIcon,
    ComponentIcon,
    HashIcon,
    GitForkIcon,
    ArrowLeftRightIcon,
    ClipboardCheckIcon,
    BadgeCheckIcon,
    ReceiptTextIcon,
    HandshakeIcon,
    KeyIcon,
    UserCogIcon,
    NetworkIcon,
} from '@/Components/Icons';
import { SVGProps } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import { useCan } from '@/hooks/useCan';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';

type NavItem = {
    routeName: string;
    labelKey: string;
    icon: (p: SVGProps<SVGSVGElement>) => JSX.Element;
    permission?: string;
};

const mainNav: NavItem[] = [
    // Dashboard
    { routeName: 'dashboard',                     labelKey: 'nav.dashboard',           icon: LayoutDashboard },
    // Organizations
    { routeName: 'organizations.index',           labelKey: 'nav.organizations',       icon: Building2,          permission: 'organizations.view' },
    { routeName: 'organization-types.index',      labelKey: 'nav.organizationTypes',   icon: TagsIcon,           permission: 'organization-types.viewAny' },
    { routeName: 'organization-units.index',      labelKey: 'nav.organizationUnits',   icon: GitBranchIcon,      permission: 'organization-units.viewAny' },
    { routeName: 'organization-unit-types.index', labelKey: 'nav.organizationUnitTypes', icon: ComponentIcon,    permission: 'organization-unit-types.viewAny' },
    { routeName: 'hierarchy-versions.index',      labelKey: 'nav.hierarchyVersions',   icon: GitForkIcon,        permission: 'hierarchy-versions.viewAny' },
    { routeName: 'code-rules.index',              labelKey: 'nav.codeRules',           icon: HashIcon,           permission: 'code-rules.viewAny' },
    // Employees
    { routeName: 'employees.index',               labelKey: 'nav.employees',           icon: Users,              permission: 'employees.view' },
    { routeName: 'employee-transfers.index',      labelKey: 'nav.employeeTransfers',   icon: ArrowLeftRightIcon, permission: 'transfers.viewAny' },
    { routeName: 'positions.index',               labelKey: 'nav.positions',           icon: Briefcase,          permission: 'positions.viewAny' },
    { routeName: 'occupations.index',             labelKey: 'nav.occupations',         icon: Briefcase,          permission: 'occupations.viewAny' },
    { routeName: 'isic-activities.index',         labelKey: 'nav.isicActivities',      icon: NetworkIcon,        permission: 'isic-activities.viewAny' },
    // ID Cards
    { routeName: 'id-cards.index',                labelKey: 'nav.idCards',             icon: CreditCard,         permission: 'cards.view' },
    { routeName: 'card-requests.index',           labelKey: 'nav.cardRequests',        icon: ClipboardCheckIcon, permission: 'card-requests.viewAny' },
    // Services
    { routeName: 'service-types.index',           labelKey: 'nav.serviceTypes',        icon: Layers,             permission: 'service-types.viewAny' },
    { routeName: 'service-providers.index',       labelKey: 'nav.providers',           icon: HandshakeIcon },
    { routeName: 'entitlements.index',            labelKey: 'nav.entitlements',        icon: BadgeCheckIcon },
    { routeName: 'entitlement-rules.index',       labelKey: 'nav.entitlementRules',    icon: ReceiptTextIcon,    permission: 'entitlement-rules.viewAny' },
    // Audit
    { routeName: 'audit-logs.index',              labelKey: 'nav.auditLogs',           icon: ScrollText,         permission: 'audit.view' },
];

const adminNav: NavItem[] = [
    { routeName: 'users.index',           labelKey: 'nav.users',          icon: UserCogIcon,  permission: 'users.viewAny' },
    { routeName: 'roles.index',           labelKey: 'nav.roles',          icon: ShieldCheck,  permission: 'roles.viewAny' },
    { routeName: 'permissions.index',     labelKey: 'nav.permissions',    icon: KeyIcon,      permission: 'permissions.viewAny' },
    { routeName: 'recycle-bin.index',     labelKey: 'nav.recycleBin',     icon: TrashIcon,    permission: 'recycle-bin.view' },
    { routeName: 'system-settings.index', labelKey: 'nav.systemSettings', icon: SettingsIcon, permission: 'system-settings.view' },
];

interface Props {
    onClose?: () => void;
    collapsed?: boolean;
    onToggleCollapse?: () => void;
}

function NavLink({ item, collapsed }: { item: NavItem; collapsed: boolean }) {
    const { can } = useCan();
    const { t } = useLocale();

    if (item.permission && !can(item.permission)) return null;

    const isActive = route().current(item.routeName);
    const Icon = item.icon;
    const label = t(item.labelKey);

    return (
        <li>
            <Link
                href={route(item.routeName)}
                title={collapsed ? label : undefined}
                className={[
                    'group flex items-center rounded-lg text-sm font-medium transition-colors',
                    'focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400',
                    collapsed ? 'justify-center px-0 py-2.5 mx-1' : 'gap-3 px-3 py-2',
                    isActive
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100',
                ].join(' ')}
                aria-current={isActive ? 'page' : undefined}
            >
                <Icon
                    className={[
                        'h-5 w-5 shrink-0',
                        isActive ? 'text-white' : 'text-slate-500 group-hover:text-slate-300',
                    ].join(' ')}
                    aria-hidden="true"
                />
                {!collapsed && <span className="truncate">{label}</span>}
            </Link>
        </li>
    );
}

function ChevronLeft({ className }: { className?: string }) {
    return (
        <svg className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    );
}

function ChevronRight({ className }: { className?: string }) {
    return (
        <svg className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    );
}

export default function AppSidebar({ onClose, collapsed = false, onToggleCollapse }: Props) {
    const { can } = useCan();
    const { t } = useLocale();
    const { getString } = useSystemSettings();

    const appName = getString('app.short_name', 'AA Employee ID');
    const orgName = getString('general.organization_name', 'Addis Ababa City Administration');
    const environmentLabel = getString('general.system_environment_label');

    const hasAnyAdmin = adminNav.some((item) =>
        !item.permission || can(item.permission),
    );

    return (
        <div className="flex h-full w-full flex-col bg-slate-900">

            {/* ── Header: logo + name (expanded) or logo + toggle (collapsed) ── */}
            {collapsed ? (
                /* Collapsed header: toggle on top, logo below */
                <div className="flex shrink-0 flex-col items-center border-b border-slate-800 py-2 gap-1">
                    {onToggleCollapse && (
                        <button
                            type="button"
                            onClick={onToggleCollapse}
                            title="Expand sidebar"
                            aria-label="Expand sidebar"
                            className="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                        >
                            <ChevronRight className="h-4 w-4" />
                        </button>
                    )}
                    <Link
                        href={route('dashboard')}
                        title={appName}
                        className="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600/10 p-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                    >
                        <ApplicationLogo className="h-full w-full object-contain fill-blue-600 dark:fill-blue-300" />
                    </Link>
                </div>
            ) : (
                /* Expanded header: logo + name on left, toggle + close on right */
                <div className="flex h-16 shrink-0 items-center gap-2 border-b border-slate-800 px-3">
                    <Link
                        href={route('dashboard')}
                        className="flex min-w-0 flex-1 items-center gap-2.5 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                    >
                        <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-600/10 p-1">
                            <ApplicationLogo className="h-full w-full object-contain fill-blue-600 dark:fill-blue-300" />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold leading-tight text-slate-100">
                                {appName}
                            </p>
                            <p className="truncate text-[10px] leading-tight text-slate-500">
                                {orgName}
                            </p>
                        </div>
                    </Link>

                    {/* Mobile close button */}
                    {onClose && (
                        <button
                            type="button"
                            onClick={onClose}
                            className="shrink-0 rounded-md p-1 text-slate-400 transition-colors hover:text-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-500"
                            aria-label="Close sidebar"
                        >
                            <X className="h-4 w-4" aria-hidden="true" />
                        </button>
                    )}

                    {/* Desktop collapse toggle — top right of header */}
                    {onToggleCollapse && (
                        <button
                            type="button"
                            onClick={onToggleCollapse}
                            title="Collapse sidebar"
                            aria-label="Collapse sidebar"
                            className="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </button>
                    )}
                </div>
            )}

            {/* ── Navigation ── */}
            <nav
                className="sidebar-scroll flex-1 overflow-y-auto py-3"
                aria-label="Main navigation"
            >
                <ul role="list" className={`space-y-0.5 ${collapsed ? '' : 'px-3'}`}>
                    {mainNav.map((item) => (
                        <NavLink key={item.routeName} item={item} collapsed={collapsed} />
                    ))}
                </ul>

                {hasAnyAdmin && (
                    <>
                        <div className={`mt-4 mb-1 ${collapsed ? 'px-0 flex justify-center' : 'px-6'}`}>
                            {collapsed ? (
                                <div className="h-px w-8 bg-slate-800" />
                            ) : (
                                <p className="text-xs font-semibold text-slate-500">
                                    {t('nav.admin')}
                                </p>
                            )}
                        </div>
                        <ul role="list" className={`space-y-0.5 ${collapsed ? '' : 'px-3'}`}>
                            {adminNav.map((item) => (
                                <NavLink key={item.routeName} item={item} collapsed={collapsed} />
                            ))}
                        </ul>
                    </>
                )}
            </nav>

            {/* ── Footer: env badge (expanded only) ── */}
            {!collapsed && (environmentLabel) && (
                <div className="shrink-0 border-t border-slate-800 px-4 py-3">
                    <p className="truncate text-[10px] text-slate-600">{orgName}</p>
                    <span className="mt-1 inline-block rounded-full bg-slate-800 px-2 py-0.5 text-[9px] font-medium uppercase tracking-wider text-slate-500">
                        {environmentLabel}
                    </span>
                </div>
            )}
        </div>
    );
}
