import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState';
import LocalizedDateDisplay from '@/Components/Calendar/LocalizedDateDisplay';
import LocalizedTimePicker from '@/Components/Calendar/LocalizedTimePicker';
import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { useLocale } from '@/hooks/useLocale';
import { useConfirm } from '@/hooks/useConfirm';

// ─── shared types ────────────────────────────────────────────────────────────
type Settings = Record<string, unknown>;
type InPageTab = 'general' | 'subsidy' | 'days' | 'scan' | 'day-rules' | 'holidays' | 'subsidy-rules' | 'reports';

type DayRule = {
    id: string; day_of_week: number; day_name: string;
    is_open: boolean; is_subsidy_day: boolean;
    open_time: string | null; close_time: string | null;
    notes: string | null;
};
type Holiday = {
    id: string; name_en: string; name_am: string | null;
    holiday_date: string; is_recurring: boolean;
    deleted_at: string | null;
    can: { update: boolean; archive: boolean };
};
type SubsidyRule = {
    id: string; code: string; name_en: string; subsidy_amount: number;
    currency: string; effective_from: string; effective_to: string | null;
    applies_to: string; is_active: boolean; deleted_at: string | null;
    can: { update: boolean; archive: boolean };
};
type CanProps = {
    update: boolean;
    updateDayRules: boolean;
    createHoliday: boolean;
    createSubsidyRule: boolean;
};

// ─── constants ───────────────────────────────────────────────────────────────
const ALL_TABS: InPageTab[] = ['general', 'subsidy', 'days', 'scan', 'day-rules', 'holidays', 'subsidy-rules', 'reports'];
const DAY_VALUES = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as const;

const AM_DAY_NAMES: Record<number, string> = {
    1: 'ሰኞ', 2: 'ማክሰኞ', 3: 'ረቡዕ', 4: 'ሐሙስ', 5: 'ዓርብ', 6: 'ቅዳሜ', 7: 'እሑድ',
};

function localizedDayName(dayOfWeek: number, locale: string): string {
    if (locale === 'am') return AM_DAY_NAMES[dayOfWeek] ?? String(dayOfWeek);
    const base = new Date(2023, 0, 2); // Monday
    base.setDate(base.getDate() + dayOfWeek - 1);
    return new Intl.DateTimeFormat(locale, { weekday: 'long' }).format(base);
}

const DAY_COLORS: Record<number, string> = {
    1: 'bg-blue-50 dark:bg-blue-950/30',  2: 'bg-blue-50 dark:bg-blue-950/30',
    3: 'bg-blue-50 dark:bg-blue-950/30',  4: 'bg-blue-50 dark:bg-blue-950/30',
    5: 'bg-blue-50 dark:bg-blue-950/30',  6: 'bg-amber-50 dark:bg-amber-950/30',
    7: 'bg-amber-50 dark:bg-amber-950/30',
};

// ─── inline DayRuleRow ───────────────────────────────────────────────────────
function DayRuleRow({ rule, canUpdate }: { rule: DayRule; canUpdate: boolean }) {
    const { t, locale } = useLocale();
    const [isOpen, setIsOpen] = useState(rule.is_open);
    const [isSubsidy, setIsSubsidy] = useState(rule.is_subsidy_day);
    const [openTime, setOpenTime] = useState(rule.open_time ?? '');
    const [closeTime, setCloseTime] = useState(rule.close_time ?? '');
    const [saving, setSaving] = useState(false);

    function save() {
        setSaving(true);
        router.patch(route('cafeteria.day-rules.update', rule.id), {
            is_open: isOpen, is_subsidy_day: isSubsidy,
            open_time: openTime || null, close_time: closeTime || null,
        }, { onFinish: () => setSaving(false), preserveScroll: true });
    }

    const timeCls = 'rounded border border-gray-300 bg-white px-2 py-1 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 disabled:opacity-50';

    return (
        <div className={`flex flex-wrap items-center gap-4 rounded-xl border border-gray-200 p-4 dark:border-slate-700 ${DAY_COLORS[rule.day_of_week]}`}>
            <div className="w-28 font-semibold text-gray-800 dark:text-slate-100">{localizedDayName(rule.day_of_week, locale)}</div>
            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                <input type="checkbox" disabled={!canUpdate} checked={isOpen} onChange={e => { setIsOpen(e.target.checked); if (!e.target.checked) setIsSubsidy(false); }} className="h-4 w-4 rounded" />
                {t('cafeteria.isOpen')}
            </label>
            <label className="flex items-center gap-2 text-sm text-gray-700 dark:text-slate-300">
                <input type="checkbox" disabled={!canUpdate || !isOpen} checked={isSubsidy} onChange={e => setIsSubsidy(e.target.checked)} className="h-4 w-4 rounded" />
                {t('cafeteria.isSubsidyDay')}
            </label>
            <div className="flex flex-wrap items-center gap-2">
                <span className="text-xs text-gray-500 dark:text-slate-400">{t('cafeteria.openTime')}</span>
                <LocalizedTimePicker value={openTime} onChange={setOpenTime} disabled={!canUpdate || !isOpen} className={timeCls} />
                <span className="text-xs text-gray-500 dark:text-slate-400">{t('cafeteria.closeTime')}</span>
                <LocalizedTimePicker value={closeTime} onChange={setCloseTime} disabled={!canUpdate || !isOpen} className={timeCls} />
            </div>
            <div className="ml-auto flex items-center gap-2">
                <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${isOpen ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-400'}`}>
                    {isOpen ? t('cafeteria.openDay') : t('cafeteria.closedDay')}
                </span>
                {isOpen && (
                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${isSubsidy ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-gray-100 text-gray-500 dark:bg-slate-800 dark:text-slate-400'}`}>
                        {isSubsidy ? t('cafeteria.isSubsidyDay') : t('common.no')}
                    </span>
                )}
                {canUpdate && (
                    <button onClick={save} disabled={saving} className="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                        {saving ? '…' : t('common.save')}
                    </button>
                )}
            </div>
        </div>
    );
}

// ─── main page ───────────────────────────────────────────────────────────────
export default function CafeteriaSettingsIndex({
    settings, can, activeTab = 'general',
    dayRules: rawDayRules,
    holidays: rawHolidays,
    holidaysYear,
    subsidyRules: rawSubsidyRules,
}: {
    settings: Settings;
    can: CanProps;
    activeTab?: string;
    dayRules?: DayRule[] | { data: DayRule[] };
    holidays?: Holiday[] | { data: Holiday[] };
    holidaysYear?: number;
    subsidyRules?: SubsidyRule[] | { data: SubsidyRule[] };
}) {
    const dayRules: DayRule[]     = Array.isArray(rawDayRules)     ? rawDayRules     : (rawDayRules     as { data: DayRule[] })?.data     ?? [];
    const holidays: Holiday[]     = Array.isArray(rawHolidays)     ? rawHolidays     : (rawHolidays     as { data: Holiday[] })?.data     ?? [];
    const subsidyRules: SubsidyRule[] = Array.isArray(rawSubsidyRules) ? rawSubsidyRules : (rawSubsidyRules as { data: SubsidyRule[] })?.data ?? [];
    const { t } = useLocale();
    const { confirm } = useConfirm();
    const [tab, setTab] = useState<InPageTab>(
        (ALL_TABS.includes(activeTab as InPageTab) ? activeTab : 'general') as InPageTab
    );
    const [form, setForm] = useState<Settings>({ ...settings });
    const [saving, setSaving] = useState(false);

    const inputCls = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 disabled:opacity-60';
    const labelCls = 'block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1';
    const sectionCls = 'rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5';
    const tableCls = 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900';
    const thCls = 'px-4 py-3 text-left text-sm font-medium text-gray-600 dark:text-slate-400';
    const tdCls = 'px-4 py-3 text-sm text-gray-700 dark:text-slate-300';

    function set(key: string, value: unknown) {
        setForm(f => ({ ...f, [key]: value }));
    }

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        setSaving(true);
        router.put(route('cafeteria.settings.update'), form as Record<string, string | number | boolean | null>, {
            onFinish: () => setSaving(false),
            preserveScroll: true,
        });
    }

    async function archiveHoliday(id: string) {
        const { confirmed } = await confirm({ title: t('confirmations.confirmDeleteTitle'), description: '', confirmLabel: t('confirmations.delete'), cancelLabel: t('confirmations.cancel'), variant: 'danger' });
        if (confirmed) router.delete(route('cafeteria.holidays.archive', id), { preserveScroll: true });
    }

    async function archiveSubsidyRule(id: string) {
        const { confirmed } = await confirm({ title: t('confirmations.confirmDeleteTitle'), description: '', confirmLabel: t('confirmations.delete'), cancelLabel: t('confirmations.cancel'), variant: 'danger' });
        if (confirmed) router.delete(route('cafeteria.subsidy-rules.archive', id), { preserveScroll: true });
    }

    const dayLabel: Record<string, string> = {
        monday: t('cafeteria.dayMonday'), tuesday: t('cafeteria.dayTuesday'),
        wednesday: t('cafeteria.dayWednesday'), thursday: t('cafeteria.dayThursday'),
        friday: t('cafeteria.dayFriday'), saturday: t('cafeteria.daySaturday'),
        sunday: t('cafeteria.daySunday'),
    };

    const tabDefs: { key: InPageTab; label: string }[] = [
        { key: 'general',       label: t('cafeteria.generalSettings') },
        { key: 'subsidy',       label: t('cafeteria.subsidySettings') },
        { key: 'days',          label: t('cafeteria.daySettings') },
        { key: 'scan',          label: t('cafeteria.scanSettings') },
        { key: 'day-rules',     label: t('cafeteria.dayRules') },
        { key: 'holidays',      label: t('nav.cafeteriaHolidays') },
        { key: 'subsidy-rules', label: t('nav.cafeteriaSubsidyRules') },
        { key: 'reports',       label: t('cafeteria.reportSettings') },
    ];

    const tabBarCls = (active: boolean) =>
        `whitespace-nowrap px-4 py-2 text-sm font-medium border-b-2 transition-colors ${
            active
                ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200'
        }`;

    const isSettingsTab = (k: InPageTab) => ['general', 'subsidy', 'days', 'scan', 'reports'].includes(k);

    return (
        <AuthenticatedLayout header={<PageHeader title={t('cafeteria.settings')} />}>
            <Head title={t('cafeteria.settings')} />
            <div className="space-y-6">
                {/* Tab bar */}
                <div className="overflow-x-auto">
                    <div className="flex min-w-max border-b border-gray-200 dark:border-slate-700">
                        {tabDefs.map(tb => (
                            <button
                                key={tb.key}
                                type="button"
                                onClick={() => setTab(tb.key)}
                                className={tabBarCls(tab === tb.key)}
                            >
                                {tb.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* ── Settings form tabs (wrap in form) ── */}
                {isSettingsTab(tab) && (
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* General */}
                        {tab === 'general' && (
                            <div className={sectionCls}>
                                <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('cafeteria.generalSettings')}</h3>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.currency')}</label>
                                        <input className={inputCls} disabled={!can.update} value={String(form.currency ?? 'ETB')} onChange={e => set('currency', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.requireActiveEmployee')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.require_active_employee ? '1' : '0'} onChange={e => set('require_active_employee', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.requireActiveIdCard')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.require_active_id_card ? '1' : '0'} onChange={e => set('require_active_id_card', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.requireProviderOperator')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.require_provider_operator ? '1' : '0'} onChange={e => set('require_provider_operator', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Subsidy */}
                        {tab === 'subsidy' && (
                            <div className={sectionCls}>
                                <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('cafeteria.subsidySettings')}</h3>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.defaultDailySubsidyAmount')}</label>
                                        <input type="number" min="0" step="0.01" className={inputCls} disabled={!can.update} value={String(form.default_daily_subsidy_amount ?? 0)} onChange={e => set('default_daily_subsidy_amount', parseFloat(e.target.value))} />
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.usageModeLabel')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.default_usage_mode ?? 'single_day')} onChange={e => set('default_usage_mode', e.target.value)}>
                                            <option value="single_day">{t('cafeteria.usageModeSingleDay')}</option>
                                            <option value="use_remaining_week">{t('cafeteria.usageModeRemainingWeek')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.allowUpfrontWeekdayUsage')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.allow_upfront_weekday_usage ? '1' : '0'} onChange={e => set('allow_upfront_weekday_usage', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.allowPastDayClaim')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.allow_past_day_claim ? '1' : '0'} onChange={e => set('allow_past_day_claim', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.allowFutureWeekBorrowing')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.allow_future_week_borrowing ? '1' : '0'} onChange={e => set('allow_future_week_borrowing', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.excessAmountMode')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.excess_amount_mode ?? 'employee_payable')} onChange={e => set('excess_amount_mode', e.target.value)}>
                                            <option value="employee_payable">{t('cafeteria.excessModeEmployeePayable')}</option>
                                            <option value="reject">{t('cafeteria.excessModeReject')}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Working Days */}
                        {tab === 'days' && (
                            <div className={sectionCls}>
                                <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('cafeteria.daySettings')}</h3>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.weekStartDay')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.week_start_day ?? 'monday')} onChange={e => set('week_start_day', e.target.value)}>
                                            {DAY_VALUES.map(d => <option key={d} value={d}>{dayLabel[d]}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.weekEndDay')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.week_end_day ?? 'friday')} onChange={e => set('week_end_day', e.target.value)}>
                                            {DAY_VALUES.map(d => <option key={d} value={d}>{dayLabel[d]}</option>)}
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.closedWeekendDefault')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.closed_weekend_default ? '1' : '0'} onChange={e => set('closed_weekend_default', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.allowSaturdayService')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.allow_saturday_service ? '1' : '0'} onChange={e => set('allow_saturday_service', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.allowSundayService')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.allow_sunday_service ? '1' : '0'} onChange={e => set('allow_sunday_service', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.excludePublicHolidays')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.exclude_public_holidays ? '1' : '0'} onChange={e => set('exclude_public_holidays', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* Scan Rules */}
                        {tab === 'scan' && (
                            <>
                            <div className={sectionCls}>
                                <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('cafeteria.scanSettings')}</h3>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.weekendScanMode')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.weekend_scan_mode ?? 'reject')} onChange={e => set('weekend_scan_mode', e.target.value)}>
                                            <option value="reject">{t('cafeteria.scanModeReject')}</option>
                                            <option value="allow">{t('cafeteria.scanModeAllow')}</option>
                                            <option value="employee_payable">{t('cafeteria.scanModeEmployeePayable')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.holidayScanMode')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.holiday_scan_mode ?? 'reject')} onChange={e => set('holiday_scan_mode', e.target.value)}>
                                            <option value="reject">{t('cafeteria.scanModeReject')}</option>
                                            <option value="allow">{t('cafeteria.scanModeAllow')}</option>
                                            <option value="employee_payable">{t('cafeteria.scanModeEmployeePayable')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.maxTransactionAmountPerScan')}</label>
                                        <input type="number" min="0" step="0.01" className={inputCls} disabled={!can.update} value={String(form.max_transaction_amount_per_scan ?? '')} placeholder={t('common.optional')} onChange={e => set('max_transaction_amount_per_scan', e.target.value ? parseFloat(e.target.value) : null)} />
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.maxExtraAmountPerWeek')}</label>
                                        <input type="number" min="0" step="0.01" className={inputCls} disabled={!can.update} value={String(form.max_extra_amount_per_week ?? '')} placeholder={t('common.optional')} onChange={e => set('max_extra_amount_per_week', e.target.value ? parseFloat(e.target.value) : null)} />
                                    </div>
                                </div>
                            </div>

                            {/* Leave Access Control */}
                            <div className={sectionCls}>
                                <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('cafeteria.leaveAccessControl')}</h3>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.blockCafeteriaDuringLeave')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.block_cafeteria_during_employee_leave ? '1' : '0'} onChange={e => set('block_cafeteria_during_employee_leave', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.leaveScanMode')}</label>
                                        <select className={inputCls} disabled={!can.update || !form.block_cafeteria_during_employee_leave} value={String(form.leave_scan_mode ?? 'reject')} onChange={e => set('leave_scan_mode', e.target.value)}>
                                            <option value="reject">{t('cafeteria.leaveScanModeReject')}</option>
                                            <option value="employee_payable">{t('cafeteria.leaveScanModeEmployeePayable')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.excludeLeaveDaysFromSubsidy')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.exclude_leave_days_from_subsidy ? '1' : '0'} onChange={e => set('exclude_leave_days_from_subsidy', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.allowLeaveDayRetroactiveClaim')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.allow_leave_day_retroactive_claim ? '1' : '0'} onChange={e => set('allow_leave_day_retroactive_claim', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.autoResumeAfterLeave')}</label>
                                        <select className={inputCls} disabled={!can.update} value={form.auto_resume_after_leave ? '1' : '0'} onChange={e => set('auto_resume_after_leave', e.target.value === '1')}>
                                            <option value="1">{t('common.yes')}</option>
                                            <option value="0">{t('common.no')}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            </>
                        )}

                        {/* Report Settings */}
                        {tab === 'reports' && (
                            <div className={sectionCls}>
                                <h3 className="text-base font-semibold text-gray-900 dark:text-slate-100">{t('cafeteria.reportSettings')}</h3>
                                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.reportType')}</label>
                                        <select className={inputCls} disabled={!can.update} value={String(form.report_default_format ?? 'csv')} onChange={e => set('report_default_format', e.target.value)}>
                                            <option value="csv">CSV</option>
                                            <option value="xlsx">Excel (XLSX)</option>
                                            <option value="pdf">PDF</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.reportTimezone')}</label>
                                        <input className={inputCls} disabled={!can.update} value={String(form.report_timezone ?? 'Africa/Addis_Ababa')} onChange={e => set('report_timezone', e.target.value)} />
                                    </div>
                                    <div>
                                        <label className={labelCls}>{t('cafeteria.payrollCutoffDay')}</label>
                                        <input type="number" min="1" max="31" className={inputCls} disabled={!can.update} value={String(form.payroll_cutoff_day ?? '')} placeholder={t('common.optional')} onChange={e => set('payroll_cutoff_day', e.target.value ? parseInt(e.target.value) : null)} />
                                    </div>
                                </div>
                            </div>
                        )}

                        {can.update && (
                            <div className="flex justify-end">
                                <button type="submit" disabled={saving} className="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                                    {saving ? t('common.saving') : t('common.save')}
                                </button>
                            </div>
                        )}
                    </form>
                )}

                {/* ── Working Day Rules tab ── */}
                {tab === 'day-rules' && (
                    <div className="space-y-3">
                        <p className="text-sm text-gray-500 dark:text-slate-400">{t('cafeteria.specialDayOverrideNote')}</p>
                        {dayRules.length === 0
                            ? <EmptyState title={t('common.noResults')} />
                            : dayRules.map(r => <DayRuleRow key={r.id} rule={r} canUpdate={can.updateDayRules} />)
                        }
                    </div>
                )}

                {/* ── Public Holidays tab ── */}
                {tab === 'holidays' && (
                    <div className="space-y-4">
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-gray-500 dark:text-slate-400">{holidaysYear}</span>
                            {can.createHoliday && (
                                <Link href={route('cafeteria.holidays.create')} className="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                    + {t('cafeteria.addHoliday')}
                                </Link>
                            )}
                        </div>
                        <div className={tableCls}>
                            {holidays.length === 0 ? <EmptyState title={t('common.noResults')} /> : (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="border-b border-gray-100 bg-gray-50 dark:border-slate-800 dark:bg-slate-800/50">
                                                <th className={thCls}>{t('cafeteria.holidayDate')}</th>
                                                <th className={thCls}>{t('cafeteria.nameEn')}</th>
                                                <th className={thCls}>{t('cafeteria.nameAm')}</th>
                                                <th className="px-4 py-3 text-center text-sm font-medium text-gray-600 dark:text-slate-400">{t('cafeteria.isRecurring')}</th>
                                                <th className="px-4 py-3" />
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                            {holidays.map(h => (
                                                <tr key={h.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/40">
                                                    <td className={`${tdCls} font-medium text-gray-900 dark:text-slate-100`}><LocalizedDateDisplay value={h.holiday_date} /></td>
                                                    <td className={tdCls}>{h.name_en}</td>
                                                    <td className={tdCls}>{h.name_am ?? '—'}</td>
                                                    <td className="px-4 py-3 text-center text-sm">{h.is_recurring ? '✓' : '—'}</td>
                                                    <td className="px-4 py-3 text-right">
                                                        <div className="flex justify-end gap-3">
                                                            {h.can.update && <Link href={route('cafeteria.holidays.edit', h.id)} className="text-xs text-blue-600 hover:underline">{t('common.edit')}</Link>}
                                                            {h.can.archive && !h.deleted_at && <button onClick={() => archiveHoliday(h.id)} className="text-xs text-red-600 hover:underline">{t('common.archive')}</button>}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                )}

                {/* ── Subsidy Rules tab ── */}
                {tab === 'subsidy-rules' && (
                    <div className="space-y-4">
                        <div className="flex justify-end">
                            {can.createSubsidyRule && (
                                <Link href={route('cafeteria.subsidy-rules.create')} className="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                    + {t('cafeteria.addSubsidyRule')}
                                </Link>
                            )}
                        </div>
                        <div className={tableCls}>
                            {subsidyRules.length === 0 ? <EmptyState title={t('common.noResults')} /> : (
                                <div className="overflow-x-auto">
                                    <table className="w-full text-sm">
                                        <thead>
                                            <tr className="border-b border-gray-100 bg-gray-50 dark:border-slate-800 dark:bg-slate-800/50">
                                                <th className={thCls}>{t('cafeteria.subsidyRule')}</th>
                                                <th className="px-4 py-3 text-right text-sm font-medium text-gray-600 dark:text-slate-400">{t('cafeteria.subsidyAmount')}</th>
                                                <th className={thCls}>{t('cafeteria.effectiveFrom')}</th>
                                                <th className={thCls}>{t('cafeteria.effectiveTo')}</th>
                                                <th className={thCls}>{t('cafeteria.appliesTo')}</th>
                                                <th className="px-4 py-3" />
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100 dark:divide-slate-800">
                                            {subsidyRules.map(rule => (
                                                <tr key={rule.id} className="hover:bg-gray-50 dark:hover:bg-slate-800/40">
                                                    <td className="px-4 py-3">
                                                        <div className="font-medium text-gray-900 dark:text-slate-100">{rule.name_en}</div>
                                                        <div className="font-mono text-xs text-gray-500">{rule.code}</div>
                                                    </td>
                                                    <td className="px-4 py-3 text-right font-medium text-emerald-600">{rule.subsidy_amount.toFixed(2)} {rule.currency}</td>
                                                    <td className={tdCls}><LocalizedDateDisplay value={rule.effective_from} /></td>
                                                    <td className={tdCls}><LocalizedDateDisplay value={rule.effective_to} /></td>
                                                    <td className={tdCls}>{rule.applies_to}</td>
                                                    <td className="px-4 py-3 text-right">
                                                        <div className="flex justify-end gap-3">
                                                            {rule.can.update && <Link href={route('cafeteria.subsidy-rules.edit', rule.id)} className="text-xs text-blue-600 hover:underline">{t('common.edit')}</Link>}
                                                            {rule.can.archive && !rule.deleted_at && <button onClick={() => archiveSubsidyRule(rule.id)} className="text-xs text-red-600 hover:underline">{t('common.archive')}</button>}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
