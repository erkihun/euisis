import { AlertTriangle, Building2, CheckCircle, CreditCard, Layers, LayoutDashboard, RefreshIcon, ShieldCheck, Store, Users } from '@/Components/Icons';

const icons = {
    activity: RefreshIcon,
    alert: AlertTriangle,
    building: Building2,
    card: CreditCard,
    coverage: CheckCircle,
    layers: Layers,
    primary: LayoutDashboard,
    queue: RefreshIcon,
    shield: ShieldCheck,
    store: Store,
    transfer: RefreshIcon,
    users: Users,
} as const;

const toneStyles = {
    primary: {
        glow: 'bg-[color:var(--color-primary)]/10',
        icon: 'bg-[color:var(--color-primary)]/10 text-[color:var(--color-primary)] ring-[color:var(--color-primary)]/15',
    },
    success: {
        glow: 'bg-emerald-500/10',
        icon: 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900/50',
    },
    warning: {
        glow: 'bg-[color:var(--color-accent)]/10',
        icon: 'bg-[color:var(--color-accent)]/10 text-[color:var(--color-accent)] ring-[color:var(--color-accent)]/15',
    },
    critical: {
        glow: 'bg-red-500/10',
        icon: 'bg-red-50 text-red-700 ring-red-100 dark:bg-red-950/50 dark:text-red-300 dark:ring-red-900/50',
    },
    neutral: {
        glow: 'bg-slate-500/10',
        icon: 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
    },
} as const;

interface Props {
    title: string;
    value: string | number;
    icon?: keyof typeof icons;
    tone?: keyof typeof toneStyles;
    trend?: number | null;
    trendDirection?: 'up' | 'down' | 'flat' | null;
    comparisonLabel?: string | null;
}

export default function KpiCard({
    title,
    value,
    icon = 'primary',
    tone = 'neutral',
    trend,
    trendDirection,
    comparisonLabel,
}: Props) {
    const Icon = icons[icon] ?? LayoutDashboard;
    const toneClass = toneStyles[tone] ?? toneStyles.neutral;
    const trendColor = trendDirection === 'up'
        ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900/50'
        : trendDirection === 'down'
            ? 'bg-red-50 text-red-700 ring-red-100 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/50'
            : 'bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700';

    return (
        <div className="group relative overflow-hidden rounded-2xl border border-gray-200/80 bg-white/95 p-5 shadow-[0_18px_45px_-28px_rgba(15,23,42,0.45)] transition duration-200 hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-[0_24px_55px_-30px_rgba(15,23,42,0.65)] dark:border-slate-800/80 dark:bg-slate-900/95 dark:hover:border-slate-700">
            <div
                className="absolute left-0 top-0 h-1 w-1/2 bg-gradient-to-r from-[var(--color-primary)] via-[color:var(--color-primary)]/45 to-transparent"
                style={{ borderTopLeftRadius: 'inherit' }}
            />
            <div className={`pointer-events-none absolute -right-10 -top-12 h-28 w-28 rounded-full blur-3xl ${toneClass.glow}`} />

            <div className="relative flex items-start justify-between gap-4">
                <div className="min-w-0">
                    <p className="truncate text-sm font-medium text-gray-500 dark:text-slate-400">{title}</p>
                    <p className="mt-3 text-3xl font-semibold tracking-tight text-gray-950 dark:text-slate-50">
                        {value}
                    </p>
                </div>
                <div className={`inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 shadow-sm transition duration-200 group-hover:scale-105 ${toneClass.icon}`}>
                    <Icon className="h-5 w-5" aria-hidden="true" />
                </div>
            </div>
            {(trend !== null && trend !== undefined) && (
                <div className="relative mt-5 flex items-center justify-between gap-3 border-t border-gray-100 pt-4 text-sm dark:border-slate-800">
                    <span className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${trendColor}`}>
                        {trend > 0 ? '+' : ''}{trend}
                    </span>
                    {comparisonLabel && (
                        <span className="truncate text-xs text-gray-500 dark:text-slate-400">{comparisonLabel}</span>
                    )}
                </div>
            )}
        </div>
    );
}
