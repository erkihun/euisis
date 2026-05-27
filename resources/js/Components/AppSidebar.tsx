import { Link, usePage } from '@inertiajs/react';
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
    TagsIcon,
    GitBranchIcon,
    HashIcon,
    GitForkIcon,
    ArrowLeftRightIcon,
    ClipboardCheckIcon,
    BadgeCheckIcon,
    ReceiptTextIcon,
    HandshakeIcon,
    KeyIcon,
    UserCogIcon,
    TrendingUpIcon,
    HardHatIcon,
    ActivityIcon,
    BoxesIcon,
    QrCodeIcon,
} from '@/Components/Icons';
import { CSSProperties, SVGProps, useMemo, useState } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import { useCan } from '@/hooks/useCan';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';

type NavItem = {
    routeName: string;
    labelKey: string;
    icon: (p: SVGProps<SVGSVGElement>) => JSX.Element;
    permission?: string;
    tab?: string; // when set, links to routeName?tab=<tab> and active only when that tab is current
};

type NavGroup = {
    key: string;
    labelKey: string;
    icon: (p: SVGProps<SVGSVGElement>) => JSX.Element;
    items: NavItem[];
};

const navGroups: NavGroup[] = [
    {
        key: 'organization',
        labelKey: 'nav.groupOrganization',
        icon: Building2,
        items: [
            { routeName: 'organizations.index',           labelKey: 'nav.organizations',        icon: Building2,      permission: 'organizations.view' },
            { routeName: 'organization-types.index',      labelKey: 'nav.organizationTypes',    icon: TagsIcon,       permission: 'organization-types.viewAny' },
            { routeName: 'organization-units.index',      labelKey: 'nav.organizationUnits',    icon: GitBranchIcon,  permission: 'organization-units.viewAny' },
            { routeName: 'organization-unit-types.index', labelKey: 'nav.organizationUnitTypes', icon: BoxesIcon,     permission: 'organization-unit-types.viewAny' },
            { routeName: 'hierarchy-versions.index',      labelKey: 'nav.hierarchyVersions',    icon: GitForkIcon,    permission: 'hierarchy-versions.viewAny' },
        ],
    },
    {
        key: 'workforce',
        labelKey: 'nav.groupWorkforce',
        icon: Users,
        items: [
            { routeName: 'employees.index',          labelKey: 'nav.employees',         icon: Users,             permission: 'employees.view' },
            { routeName: 'employee-transfers.index', labelKey: 'nav.employeeTransfers', icon: ArrowLeftRightIcon, permission: 'transfers.viewAny' },
            { routeName: 'positions.index',          labelKey: 'nav.positions',         icon: Briefcase,         permission: 'positions.viewAny' },
            { routeName: 'grade-levels.index',       labelKey: 'nav.gradeLevels',       icon: TrendingUpIcon,    permission: 'grade-levels.viewAny' },
            { routeName: 'occupations.index',        labelKey: 'nav.occupations',       icon: HardHatIcon,       permission: 'occupations.viewAny' },
            { routeName: 'isic-activities.index',    labelKey: 'nav.isicActivities',    icon: ActivityIcon,      permission: 'isic-activities.viewAny' },
        ],
    },
    {
        key: 'identity',
        labelKey: 'nav.groupIdentity',
        icon: CreditCard,
        items: [
            { routeName: 'id-cards.index',      labelKey: 'nav.idCards',      icon: CreditCard,       permission: 'cards.view' },
            { routeName: 'card-requests.index', labelKey: 'nav.cardRequests', icon: ClipboardCheckIcon, permission: 'card-requests.viewAny' },
        ],
    },
    {
        key: 'services',
        labelKey: 'nav.groupServices',
        icon: Layers,
        items: [
            { routeName: 'service-types.index',      labelKey: 'nav.serviceTypes',    icon: Layers,         permission: 'service-types.viewAny' },
            { routeName: 'service-providers.index',  labelKey: 'nav.providers',       icon: HandshakeIcon },
            { routeName: 'entitlements.index',       labelKey: 'nav.entitlements',    icon: BadgeCheckIcon },
            { routeName: 'entitlement-rules.index',  labelKey: 'nav.entitlementRules', icon: ReceiptTextIcon, permission: 'entitlement-rules.viewAny' },
        ],
    },
    {
        key: 'cafeteria',
        labelKey: 'nav.groupCafeteria',
        icon: QrCodeIcon,
        items: [
            { routeName: 'cafeteria.dashboard',           labelKey: 'nav.cafeteriaDashboard',    icon: LayoutDashboard,  permission: 'cafeteria_transactions.viewAny' },
            { routeName: 'cafeteria.scan',                labelKey: 'nav.cafeteriaScan',         icon: QrCodeIcon,       permission: 'cafeteria_transactions.scan' },
            { routeName: 'cafeteria.transactions.index',  labelKey: 'nav.cafeteriaTransactions', icon: ReceiptTextIcon,  permission: 'cafeteria_transactions.viewAny' },
            { routeName: 'cafeteria.ledger.index',        labelKey: 'nav.cafeteriaLedger',       icon: ScrollText,       permission: 'cafeteria_ledger.viewAny' },
            { routeName: 'cafeteria.reports.index',       labelKey: 'nav.cafeteriaReports',      icon: ActivityIcon,     permission: 'cafeteria_reports.viewAny' },
            { routeName: 'cafeteria.providers.index',     labelKey: 'nav.cafeteriaProviders',    icon: HandshakeIcon,    permission: 'cafeteria_providers.viewAny' },
            { routeName: 'cafeteria.settings.index', labelKey: 'nav.cafeteriaSettings', icon: SettingsIcon, permission: 'cafeteria_settings.view' },
        ],
    },
    {
        key: 'configuration',
        labelKey: 'nav.groupConfiguration',
        icon: SettingsIcon,
        items: [
            { routeName: 'code-rules.index',  labelKey: 'nav.codeRules', icon: HashIcon,    permission: 'code-rules.viewAny' },
            { routeName: 'audit-logs.index',  labelKey: 'nav.auditLogs', icon: ScrollText,  permission: 'audit.view' },
        ],
    },
];

const dashboardNav: NavItem = {
    routeName: 'dashboard',
    labelKey: 'nav.dashboard',
    icon: LayoutDashboard,
};

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
function ChevronDown({ className }: { className?: string }) {
    return (
        <svg className={className} fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    );
}

/** Single nav item — expanded or icon-only collapsed */
function NavLink({ item, collapsed, isAdmin = false }: { item: NavItem; collapsed: boolean; isAdmin?: boolean }) {
    const { can } = useCan();
    const { t } = useLocale();
    const { url: pageUrl } = usePage();

    if (item.permission && !can(item.permission)) return null;

    const href = item.tab ? `${route(item.routeName)}?tab=${item.tab}` : route(item.routeName);
    const isActive = item.tab
        ? route().current(item.routeName) && (() => {
            const currentTab = new URLSearchParams(pageUrl.split('?')[1] ?? '').get('tab') ?? 'general';
            return currentTab === item.tab;
        })()
        : route().current(item.routeName);
    const Icon = item.icon;
    const label = t(item.labelKey);

    const activeBar = 'border-[var(--color-primary)] bg-[color:var(--color-primary)]/10';
    const activeText = 'text-[color:var(--color-primary)]';
    const activeIcon  = activeText;
    const hoverBg = 'hover:bg-[color:var(--color-primary)]/10 hover:text-gray-900 dark:hover:text-slate-100';

    if (collapsed) {
        return (
            <li>
                <Link
                    href={href}
                    title={label}
                    aria-label={label}
                    aria-current={isActive ? 'page' : undefined}
                    className={[
                        'group relative mx-2 flex h-10 w-10 items-center justify-center rounded-lg transition-colors',
                        'focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]',
                        isActive
                            ? `${activeBar} ${activeText}`
                            : `text-gray-500 dark:text-slate-500 ${hoverBg}`,
                    ].join(' ')}
                >
                    <Icon
                        className={[
                            'h-5 w-5 shrink-0 transition-colors',
                            isActive ? activeIcon : 'text-gray-500 group-hover:text-gray-800 dark:text-slate-500 dark:group-hover:text-slate-300',
                        ].join(' ')}
                        aria-hidden="true"
                    />
                </Link>
            </li>
        );
    }

    return (
        <li>
            <Link
                href={href}
                aria-current={isActive ? 'page' : undefined}
                className={[
                    'group flex items-center gap-3 rounded-lg py-2.5 pl-3 pr-3 text-[15px] font-normal transition-colors',
                    'focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]',
                    isActive
                        ? `border-l-2 ${activeBar} ${activeText}`
                        : `border-l-2 border-transparent text-gray-600 dark:text-slate-400 ${hoverBg}`,
                ].join(' ')}
            >
                <Icon
                    className={[
                        'h-[19px] w-[19px] shrink-0 transition-colors',
                        isActive ? activeIcon : 'text-gray-500 group-hover:text-gray-800 dark:text-slate-500 dark:group-hover:text-slate-300',
                    ].join(' ')}
                    aria-hidden="true"
                />
                <span className="truncate">{label}</span>
            </Link>
        </li>
    );
}

export default function AppSidebar({ onClose, collapsed = false, onToggleCollapse }: Props) {
    const { can } = useCan();
    const { locale, t } = useLocale();
    const { getString } = useSystemSettings();

    const appName        = getString('app.short_name',              'AA Employee ID');
    const orgName        = getString('general.organization_name',   'Addis Ababa City Administration');
    const environmentLabel = getString('general.system_environment_label');
    const sidebarStyle: CSSProperties | undefined = locale === 'am'
        ? { fontFamily: 'var(--font-ethiopic)' }
        : undefined;

    const visibleGroups = useMemo(() =>
        navGroups
            .map((g) => ({ ...g, items: g.items.filter((item) => !item.permission || can(item.permission)) }))
            .filter((g) => g.items.length > 0),
        [can],
    );

    const visibleAdminNav = useMemo(() =>
        adminNav.filter((item) => !item.permission || can(item.permission)),
        [can],
    );

    const activeGroupKeys = useMemo(() => new Set(
        [...visibleGroups, ...(visibleAdminNav.length > 0 ? [{ key: 'admin', labelKey: 'nav.admin', icon: ShieldCheck, items: visibleAdminNav }] : [])]
            .filter((g) => g.items.some((item) => route().current(item.routeName)))
            .map((g) => g.key),
    ), [visibleGroups, visibleAdminNav]);

    const [openGroups, setOpenGroups] = useState<Record<string, boolean>>(() => {
        const defaults: Record<string, boolean> = {};
        for (const g of visibleGroups) {
            defaults[g.key] = g.items.some((item) => route().current(item.routeName));
        }
        if (visibleAdminNav.length > 0) {
            defaults.admin = visibleAdminNav.some((item) => route().current(item.routeName));
        }
        return defaults;
    });

    const toggleGroup = (key: string) =>
        setOpenGroups((cur) => ({ ...cur, [key]: !(cur[key] ?? false) }));

    const renderGroup = (group: NavGroup, isAdmin = false) => {
        const GroupIcon = group.icon;
        const isActive  = activeGroupKeys.has(group.key);
        const isOpen    = openGroups[group.key] ?? isActive;
        const label     = t(group.labelKey);

        if (collapsed) {
            return (
                <ul key={group.key} role="list" className="space-y-0.5 py-0.5">
                    {group.items.map((item) => (
                        <NavLink key={item.routeName} item={item} collapsed isAdmin={isAdmin} />
                    ))}
                </ul>
            );
        }

        const headerActive  = 'text-[color:var(--color-primary)]';
        const headerDefault = 'text-gray-500 hover:text-gray-900 dark:text-slate-500 dark:hover:text-slate-300';
        const iconActive    = 'text-[color:var(--color-primary)]';

        return (
            <div key={group.key}>
                <button
                    type="button"
                    onClick={() => toggleGroup(group.key)}
                    aria-expanded={isOpen}
                    className={[
                        'flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-left transition-colors',
                        'focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]',
                        isActive ? headerActive : headerDefault,
                    ].join(' ')}
                >
                    <GroupIcon
                        className={['h-4 w-4 shrink-0', isActive ? iconActive : 'text-gray-400 dark:text-slate-600'].join(' ')}
                        aria-hidden="true"
                    />
                    <span className="min-w-0 flex-1 truncate text-sm font-medium">
                        {label}
                    </span>
                    <ChevronDown
                        className={['h-3.5 w-3.5 shrink-0 transition-transform duration-200', isOpen ? '' : '-rotate-90'].join(' ')}
                    />
                </button>

                {isOpen && (
                    <ul role="list" className="mt-0.5 mb-1 space-y-0.5 pl-1">
                        {group.items.map((item) => (
                            <NavLink key={item.routeName} item={item} collapsed={false} isAdmin={isAdmin} />
                        ))}
                    </ul>
                )}
            </div>
        );
    };

    return (
        <div
            className="flex h-full w-full flex-col border-r border-gray-200 bg-white text-gray-700 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
            style={sidebarStyle}
            data-sidebar
        >

            {/* ── Header ─────────────────────────────────────────────────────── */}
            {collapsed ? (
                <div className="flex shrink-0 flex-col items-center gap-2 py-3">
                    {onToggleCollapse && (
                        <button
                            type="button"
                            onClick={onToggleCollapse}
                            title="Expand sidebar"
                            aria-label="Expand sidebar"
                            className="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] dark:text-slate-500 dark:hover:bg-white/8 dark:hover:text-slate-200"
                        >
                            <ChevronRight className="h-3.5 w-3.5" />
                        </button>
                    )}
                    <Link
                        href={route('dashboard')}
                        title={appName}
                        className="flex h-8 w-8 items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)]"
                    >
                        <ApplicationLogo className="h-full w-full fill-slate-900 object-contain dark:fill-white" />
                    </Link>
                </div>
            ) : (
                <div className="flex h-[60px] shrink-0 items-center gap-2.5 px-4">
                    <Link
                        href={route('dashboard')}
                        className="flex min-w-0 flex-1 items-center gap-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] rounded-lg"
                    >
                        <div className="flex h-8 w-8 shrink-0 items-center justify-center">
                            <ApplicationLogo className="h-full w-full fill-slate-900 object-contain dark:fill-white" />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-[15px] font-medium leading-tight text-gray-950 dark:text-white">
                                {appName}
                            </p>
                            <p className="truncate text-xs leading-tight text-gray-500 dark:text-slate-500">
                                {orgName}
                            </p>
                        </div>
                    </Link>

                    {onClose && (
                        <button
                            type="button"
                            onClick={onClose}
                            aria-label="Close sidebar"
                            className="shrink-0 rounded-md p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] dark:text-slate-500 dark:hover:bg-white/8 dark:hover:text-slate-200"
                        >
                            <X className="h-4 w-4" aria-hidden="true" />
                        </button>
                    )}

                    {onToggleCollapse && (
                        <button
                            type="button"
                            onClick={onToggleCollapse}
                            title="Collapse sidebar"
                            aria-label="Collapse sidebar"
                            className="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary)] dark:text-slate-500 dark:hover:bg-white/8 dark:hover:text-slate-200"
                        >
                            <ChevronLeft className="h-3.5 w-3.5" />
                        </button>
                    )}
                </div>
            )}

            {/* ── Navigation ─────────────────────────────────────────────────── */}
            <nav className="sidebar-scroll flex-1 overflow-y-auto py-3" aria-label="Main navigation">

                {/* Dashboard — standalone top item */}
                <ul role="list" className={collapsed ? 'space-y-0.5 py-0.5' : 'px-3 pb-2'}>
                    <NavLink item={dashboardNav} collapsed={collapsed} />
                </ul>

                {/* Main groups */}
                {!collapsed && <div className="mx-3 mb-2 h-px bg-gray-200 dark:bg-white/5" />}

                <div className={['space-y-0.5', collapsed ? '' : 'px-3'].join(' ')}>
                    {visibleGroups.map((g) => renderGroup(g, false))}
                </div>

                {/* Administration section */}
                {visibleAdminNav.length > 0 && (
                    <div className="mt-3">
                        {collapsed ? (
                            <>
                                <div className="mx-auto mb-1 h-px w-8 bg-gray-200 dark:bg-white/5" />
                                <ul role="list" className="space-y-0.5 py-0.5">
                                    {visibleAdminNav.map((item) => (
                                        <NavLink key={item.routeName} item={item} collapsed isAdmin />
                                    ))}
                                </ul>
                            </>
                        ) : (
                            <div className="px-3">
                                <div className="mb-1 flex items-center gap-2">
                                    <div className="h-px flex-1 bg-gray-200 dark:bg-white/5" />
                                    <span className="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-slate-500">
                                        {t('nav.admin')}
                                    </span>
                                    <div className="h-px flex-1 bg-gray-200 dark:bg-white/5" />
                                </div>
                                {renderGroup(
                                    { key: 'admin', labelKey: 'nav.admin', icon: ShieldCheck, items: visibleAdminNav },
                                    true,
                                )}
                            </div>
                        )}
                    </div>
                )}
            </nav>

            {/* ── Footer ─────────────────────────────────────────────────────── */}
            {!collapsed && (
                <div className="shrink-0 border-t border-gray-200 px-4 py-3 dark:border-white/5">
                    <div className="flex items-center justify-between gap-2">
                        <p className="min-w-0 truncate text-xs text-gray-500 dark:text-slate-500">{orgName}</p>
                        {environmentLabel && (
                            <span className="shrink-0 rounded-full bg-[color:var(--color-accent)]/10 px-2 py-0.5 text-[11px] font-medium text-[color:var(--color-accent)]">
                                {environmentLabel}
                            </span>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
