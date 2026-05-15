import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState';
import { Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

type Permission = {
    id: number;
    name: string;
    guard_name: string;
    label_en: string | null;
    label_am: string | null;
    description_en: string | null;
    description_am: string | null;
    group: string | null;
    sort_order: number;
    is_system: boolean;
    roles_count: number | null;
};

const CRITICAL = new Set([
    'users.assignRoles',
    'roles.assignPermissions',
    'permissions.delete',
    'system-settings.manageSecurity',
    'recycle-bin.forceDelete',
]);

const inputCls =
    'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500';

export default function PermissionsIndex({
    permissions,
    groups,
    filters,
    can,
}: {
    permissions: Permission[];
    groups: string[];
    filters: { search: string; group: string };
    can: { create: boolean };
}) {
    const { t, locale } = useLocale();
    const [view, setView] = useState<'table' | 'grouped'>('grouped');

    function label(p: Permission): string {
        return (locale === 'am' ? p.label_am : null) ?? p.label_en ?? p.name;
    }

    function description(p: Permission): string | null {
        return (locale === 'am' ? p.description_am : null) ?? p.description_en ?? null;
    }

    function applyFilter(key: string, value: string) {
        router.get(route('permissions.index'), { ...filters, [key]: value }, { preserveState: true, replace: true });
    }

    function destroy(id: number, name: string) {
        if (!confirm(`${t('common.delete')} "${name}"? ${t('common.cannotUndo')}`)) return;
        router.delete(route('permissions.destroy', id), { preserveScroll: true });
    }

    const grouped = permissions.reduce<Record<string, Permission[]>>((acc, p) => {
        const key = p.group ?? 'other';
        (acc[key] ??= []).push(p);
        return acc;
    }, {});

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={t('permissions.title')}
                    description={t('permissions.groupedByModule')}
                    actions={
                        can.create ? (
                            <Link
                                href={route('permissions.create')}
                                className="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-900"
                            >
                                <Plus className="h-3.5 w-3.5" aria-hidden="true" />
                                {t('permissions.createPermission')}
                            </Link>
                        ) : undefined
                    }
                />
            }
        >
            <Head title={t('permissions.title')} />

            {/* Toolbar */}
            <div className="mb-4 flex flex-wrap items-center gap-3">
                <input
                    type="search"
                    placeholder={t('permissions.searchPermissions')}
                    defaultValue={filters.search}
                    onChange={(e) => applyFilter('search', e.target.value)}
                    className={`${inputCls} w-64`}
                />
                <select
                    defaultValue={filters.group}
                    onChange={(e) => applyFilter('group', e.target.value)}
                    className={`${inputCls} w-48`}
                >
                    <option value="">{t('permissions.allGroups')}</option>
                    {groups.map((g) => (
                        <option key={g} value={g}>{g}</option>
                    ))}
                </select>
                <div className="ml-auto flex items-center rounded-lg border border-gray-200 dark:border-slate-700">
                    <button
                        type="button"
                        onClick={() => setView('grouped')}
                        className={`rounded-l-lg px-3 py-1.5 text-xs font-medium transition-colors ${view === 'grouped' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50 dark:text-slate-400 dark:hover:bg-slate-800'}`}
                    >
                        {t('permissions.groupedView')}
                    </button>
                    <button
                        type="button"
                        onClick={() => setView('table')}
                        className={`rounded-r-lg px-3 py-1.5 text-xs font-medium transition-colors ${view === 'table' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-50 dark:text-slate-400 dark:hover:bg-slate-800'}`}
                    >
                        {t('permissions.tableView')}
                    </button>
                </div>
            </div>

            {permissions.length === 0 ? (
                <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <EmptyState title={t('permissions.noPermissionsFound')} description="" />
                </div>
            ) : view === 'table' ? (
                <TableView
                    permissions={permissions}
                    label={label}
                    description={description}
                    onDestroy={destroy}
                    t={t}
                />
            ) : (
                <GroupedView
                    grouped={grouped}
                    label={label}
                    description={description}
                    onDestroy={destroy}
                    t={t}
                />
            )}
        </AuthenticatedLayout>
    );
}

function SystemBadge({ t }: { t: (key: string) => string }) {
    return (
        <span className="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
            {t('permissions.systemPermission')}
        </span>
    );
}

function CriticalBadge({ t }: { t: (key: string) => string }) {
    return (
        <span
            title={t('permissions.criticalPermissionWarning')}
            className="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400"
        >
            {t('permissions.criticalPermission')}
        </span>
    );
}

function TableView({
    permissions,
    label,
    description,
    onDestroy,
    t,
}: {
    permissions: Permission[];
    label: (p: Permission) => string;
    description: (p: Permission) => string | null;
    onDestroy: (id: number, name: string) => void;
    t: (key: string) => string;
}) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div className="overflow-x-auto">
                <table className="min-w-full text-left text-sm">
                    <thead className="bg-gray-50 dark:bg-slate-950">
                        <tr>
                            {[
                                t('permissions.permissionKey'),
                                t('permissions.permissionLabel'),
                                t('permissions.permissionDescription'),
                                t('permissions.permissionGroup'),
                                t('permissions.guardName'),
                                t('permissions.rolesCount'),
                                '',
                            ].map((h) => (
                                <th
                                    key={h}
                                    className="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 first:pl-5 last:pr-5 dark:text-slate-400"
                                >
                                    {h}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {permissions.map((p) => (
                            <tr
                                key={p.id}
                                className="border-t border-gray-100 hover:bg-gray-50 dark:border-slate-800 dark:hover:bg-slate-800/50"
                            >
                                <td className="py-3 pl-5 pr-4">
                                    <div className="flex flex-wrap items-center gap-1.5">
                                        <span className="font-mono text-xs text-gray-700 dark:text-slate-300">{p.name}</span>
                                        {p.is_system && <SystemBadge t={t} />}
                                        {CRITICAL.has(p.name) && <CriticalBadge t={t} />}
                                    </div>
                                </td>
                                <td className="px-4 py-3 text-gray-700 dark:text-slate-300">{label(p)}</td>
                                <td className="max-w-xs px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                                    <span className="line-clamp-2">{description(p)}</span>
                                </td>
                                <td className="px-4 py-3">
                                    {p.group && (
                                        <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-slate-800 dark:text-slate-300">
                                            {p.group}
                                        </span>
                                    )}
                                </td>
                                <td className="px-4 py-3 font-mono text-xs text-gray-500 dark:text-slate-400">{p.guard_name}</td>
                                <td className="px-4 py-3 tabular-nums text-gray-500 dark:text-slate-400">{p.roles_count ?? 0}</td>
                                <td className="py-3 pl-4 pr-5">
                                    <RowActions p={p} onDestroy={onDestroy} t={t} />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

function GroupedView({
    grouped,
    label,
    description,
    onDestroy,
    t,
}: {
    grouped: Record<string, Permission[]>;
    label: (p: Permission) => string;
    description: (p: Permission) => string | null;
    onDestroy: (id: number, name: string) => void;
    t: (key: string) => string;
}) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {Object.entries(grouped).map(([group, perms]) => (
                <div
                    key={group}
                    className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"
                >
                    <div className="mb-3 flex items-center justify-between">
                        <h3 className="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                            {group}
                        </h3>
                        <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            {perms.length}
                        </span>
                    </div>
                    <ul className="space-y-2.5">
                        {perms.map((p) => (
                            <li key={p.id} className="group/row flex items-start justify-between gap-2">
                                <div className="min-w-0">
                                    <div className="flex flex-wrap items-center gap-1">
                                        <span className="font-mono text-xs text-gray-700 dark:text-slate-300">{p.name}</span>
                                        {p.is_system && <SystemBadge t={t} />}
                                        {CRITICAL.has(p.name) && <CriticalBadge t={t} />}
                                    </div>
                                    {label(p) !== p.name && (
                                        <p className="mt-0.5 text-xs font-medium text-gray-900 dark:text-slate-100">{label(p)}</p>
                                    )}
                                    {description(p) && (
                                        <p className="mt-0.5 line-clamp-2 text-xs text-gray-500 dark:text-slate-400">
                                            {description(p)}
                                        </p>
                                    )}
                                </div>
                                <div className="shrink-0 opacity-0 transition-opacity group-hover/row:opacity-100">
                                    <RowActions p={p} onDestroy={onDestroy} t={t} />
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            ))}
        </div>
    );
}

function RowActions({
    p,
    onDestroy,
    t,
}: {
    p: Permission;
    onDestroy: (id: number, name: string) => void;
    t: (key: string) => string;
}) {
    return (
        <div className="flex items-center gap-2">
            <Link
                href={route('permissions.edit', p.id)}
                className="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            >
                {t('common.edit')}
            </Link>
            {!p.is_system && (
                <button
                    type="button"
                    onClick={() => onDestroy(p.id, p.name)}
                    className="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                >
                    {t('common.delete')}
                </button>
            )}
        </div>
    );
}
