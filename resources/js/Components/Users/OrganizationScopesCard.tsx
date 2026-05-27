import { useState } from 'react';
import { router } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';
import LocalizedDatePicker from '@/Components/Calendar/LocalizedDatePicker';

type OrganizationScope = {
    id: string;
    organization: { id: string; name_en: string; name_am?: string } | null;
    scope_type: 'self' | 'subtree' | 'citywide' | 'service_provider';
    effective_from: string | null;
    effective_to: string | null;
    is_active: boolean;
};

type OrgOption = { id: string; name_en: string; name_am?: string };

type Props = {
    userId: string;
    scopes: OrganizationScope[];
    organizations: OrgOption[];
    canManage: boolean;
};

type FormState = {
    organization_id: string;
    scope_type: OrganizationScope['scope_type'];
    effective_from: string;
    effective_to: string;
    is_active: boolean;
};

const emptyForm = (): FormState => ({
    organization_id: '',
    scope_type: 'self',
    effective_from: '',
    effective_to: '',
    is_active: true,
});

const inputCls =
    'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder-slate-500';
const labelCls = 'block text-xs font-medium text-gray-600 dark:text-slate-400';

export default function OrganizationScopesCard({ userId, scopes, organizations, canManage }: Props) {
    const { t, locale } = useLocale();
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState<string | null>(null);
    const [form, setForm] = useState<FormState>(emptyForm());
    const [orgSearch, setOrgSearch] = useState('');

    function scopeTypeLabel(st: OrganizationScope['scope_type']): string {
        return t(`users.userOrganizationScopes.scopeTypes.${st}`);
    }

    function orgName(org: OrgOption | null | undefined): string {
        if (!org) return '—';
        return (locale === 'am' && org.name_am) ? org.name_am : org.name_en;
    }

    function filteredOrgs(): OrgOption[] {
        const q = orgSearch.toLowerCase();
        return organizations.filter(
            (o) =>
                o.name_en.toLowerCase().includes(q) ||
                (o.name_am ?? '').toLowerCase().includes(q),
        );
    }

    function openAdd() {
        setForm(emptyForm());
        setOrgSearch('');
        setEditingId(null);
        setShowForm(true);
    }

    function openEdit(scope: OrganizationScope) {
        setForm({
            organization_id: scope.organization?.id ?? '',
            scope_type: scope.scope_type,
            effective_from: scope.effective_from ?? '',
            effective_to: scope.effective_to ?? '',
            is_active: scope.is_active,
        });
        setOrgSearch(scope.organization ? orgName(scope.organization) : '');
        setEditingId(scope.id);
        setShowForm(true);
    }

    function cancelForm() {
        setShowForm(false);
        setEditingId(null);
        setForm(emptyForm());
        setOrgSearch('');
    }

    function submitForm() {
        const base = {
            scope_type: form.scope_type,
            effective_from: form.effective_from || null,
            effective_to: form.effective_to || null,
            is_active: form.is_active,
        };

        const payload =
            form.scope_type !== 'citywide'
                ? { ...base, organization_id: form.organization_id }
                : base;

        if (editingId) {
            router.put(route('users.organization-scopes.update', { user: userId, scope: editingId }), payload, {
                preserveScroll: true,
                onSuccess: cancelForm,
            });
        } else {
            router.post(route('users.organization-scopes.store', { user: userId }), payload, {
                preserveScroll: true,
                onSuccess: cancelForm,
            });
        }
    }

    function removeScope(scopeId: string) {
        if (!window.confirm(t('users.userOrganizationScopes.confirmRemove'))) return;
        router.delete(route('users.organization-scopes.destroy', { user: userId, scope: scopeId }), {
            preserveScroll: true,
        });
    }

    return (
        <div className="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <div className="mb-4 flex items-center justify-between">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-slate-100">
                    {t('users.userOrganizationScopes.title')}
                </h3>
                {canManage && !showForm && (
                    <button
                        type="button"
                        onClick={openAdd}
                        className="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                    >
                        {t('users.userOrganizationScopes.addScope')}
                    </button>
                )}
            </div>

            {scopes.length === 0 && !showForm && (
                <p className="text-sm text-gray-500 dark:text-slate-400">
                    {t('users.userOrganizationScopes.noScopes')}
                </p>
            )}

            {scopes.length > 0 && (
                <div className="mb-4 overflow-x-auto">
                    <table className="w-full text-left text-sm">
                        <thead>
                            <tr className="border-b border-gray-100 dark:border-slate-800">
                                <th className={`${labelCls} pb-2 pr-4`}>{t('users.userOrganizationScopes.organization')}</th>
                                <th className={`${labelCls} pb-2 pr-4`}>{t('users.userOrganizationScopes.scopeType')}</th>
                                <th className={`${labelCls} pb-2 pr-4`}>{t('users.userOrganizationScopes.effectiveFrom')}</th>
                                <th className={`${labelCls} pb-2 pr-4`}>{t('users.userOrganizationScopes.effectiveTo')}</th>
                                <th className={`${labelCls} pb-2 pr-4`}>{t('users.userOrganizationScopes.isActive')}</th>
                                {canManage && <th className={`${labelCls} pb-2`}></th>}
                            </tr>
                        </thead>
                        <tbody>
                            {scopes.map((scope) => (
                                <tr
                                    key={scope.id}
                                    className="border-b border-gray-50 last:border-0 dark:border-slate-800"
                                >
                                    <td className="py-2 pr-4 text-gray-800 dark:text-slate-200">
                                        {scope.scope_type === 'citywide'
                                            ? '—'
                                            : orgName(scope.organization)}
                                    </td>
                                    <td className="py-2 pr-4 text-gray-700 dark:text-slate-300">
                                        {scopeTypeLabel(scope.scope_type)}
                                    </td>
                                    <td className="py-2 pr-4 text-gray-600 dark:text-slate-400">
                                        {scope.effective_from ?? '—'}
                                    </td>
                                    <td className="py-2 pr-4 text-gray-600 dark:text-slate-400">
                                        {scope.effective_to ?? '—'}
                                    </td>
                                    <td className="py-2 pr-4">
                                        <span
                                            className={
                                                scope.is_active
                                                    ? 'rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-slate-800 dark:text-slate-400'
                                            }
                                        >
                                            {scope.is_active ? t('common.active') : t('common.inactive')}
                                        </span>
                                    </td>
                                    {canManage && (
                                        <td className="py-2 text-right">
                                            <button
                                                type="button"
                                                onClick={() => openEdit(scope)}
                                                className="mr-2 text-xs text-blue-600 hover:underline dark:text-blue-400"
                                            >
                                                {t('common.edit')}
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => removeScope(scope.id)}
                                                className="text-xs text-red-600 hover:underline dark:text-red-400"
                                            >
                                                {t('users.userOrganizationScopes.remove')}
                                            </button>
                                        </td>
                                    )}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {showForm && (
                <div className="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
                    <div className="space-y-3">
                        <div>
                            <label className={labelCls}>{t('users.userOrganizationScopes.scopeType')}</label>
                            <div className="mt-1">
                                <select
                                    className={inputCls}
                                    value={form.scope_type}
                                    onChange={(e) =>
                                        setForm((f) => ({
                                            ...f,
                                            scope_type: e.target.value as OrganizationScope['scope_type'],
                                            organization_id: e.target.value === 'citywide' ? '' : f.organization_id,
                                        }))
                                    }
                                >
                                    {(['self', 'subtree', 'citywide', 'service_provider'] as const).map((st) => (
                                        <option key={st} value={st}>
                                            {scopeTypeLabel(st)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {form.scope_type !== 'citywide' && (
                            <div>
                                <label className={labelCls}>{t('users.userOrganizationScopes.organization')}</label>
                                <div className="mt-1 space-y-1">
                                    <input
                                        className={inputCls}
                                        placeholder={t('common.search')}
                                        value={orgSearch}
                                        onChange={(e) => {
                                            setOrgSearch(e.target.value);
                                            setForm((f) => ({ ...f, organization_id: '' }));
                                        }}
                                    />
                                    {orgSearch.length > 0 && form.organization_id === '' && (
                                        <ul className="max-h-40 overflow-y-auto rounded-lg border border-gray-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                                            {filteredOrgs().map((o) => (
                                                <li key={o.id}>
                                                    <button
                                                        type="button"
                                                        className="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-slate-800"
                                                        onClick={() => {
                                                            setForm((f) => ({ ...f, organization_id: o.id }));
                                                            setOrgSearch(orgName(o));
                                                        }}
                                                    >
                                                        {orgName(o)}
                                                    </button>
                                                </li>
                                            ))}
                                        </ul>
                                    )}
                                </div>
                                {form.scope_type === 'self' && (
                                    <p className="mt-1 text-xs text-gray-500 dark:text-slate-400">
                                        {t('users.userOrganizationScopes.selfHelperText')}
                                    </p>
                                )}
                            </div>
                        )}

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className={labelCls}>{t('users.userOrganizationScopes.effectiveFrom')}</label>
                                <div className="mt-1">
                                    <LocalizedDatePicker
                                        className={inputCls}
                                        value={form.effective_from}
                                        onChange={(iso) => setForm((f) => ({ ...f, effective_from: iso }))}
                                    />
                                </div>
                            </div>
                            <div>
                                <label className={labelCls}>{t('users.userOrganizationScopes.effectiveTo')}</label>
                                <div className="mt-1">
                                    <LocalizedDatePicker
                                        className={inputCls}
                                        value={form.effective_to}
                                        onChange={(iso) => setForm((f) => ({ ...f, effective_to: iso }))}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                id="scope-is-active"
                                className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-slate-600"
                                checked={form.is_active}
                                onChange={(e) => setForm((f) => ({ ...f, is_active: e.target.checked }))}
                            />
                            <label htmlFor="scope-is-active" className="text-sm text-gray-700 dark:text-slate-300">
                                {t('users.userOrganizationScopes.isActive')}
                            </label>
                        </div>
                    </div>

                    <div className="mt-4 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            onClick={cancelForm}
                            className="rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-800"
                        >
                            {t('users.userOrganizationScopes.cancel')}
                        </button>
                        <button
                            type="button"
                            onClick={submitForm}
                            disabled={form.scope_type !== 'citywide' && form.organization_id === ''}
                            className="rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-slate-900"
                        >
                            {t('users.userOrganizationScopes.save')}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}
