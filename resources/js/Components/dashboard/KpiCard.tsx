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
    primary: 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950/40 dark:text-blue-300 dark:ring-blue-900/40',
    success: 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900/40',
    warning: 'bg-orange-50 text-orange-700 ring-orange-100 dark:bg-orange-950/40 dark:text-orange-300 dark:ring-orange-900/40',
    critical: 'bg-red-50 text-red-700 ring-red-100 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/40',
    neutral: 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
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
    const trendColor = trendDirection === 'up'
        ? 'text-emerald-600 dark:text-emerald-400'
        : trendDirection === 'down'
            ? 'text-red-600 dark:text-red-400'
            : 'text-slate-500 dark:text-slate-400';

    return (
        <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-sm font-medium text-gray-500 dark:text-slate-400">{title}</p>
                    <p className="mt-3 text-3xl font-semibold tracking-tight text-gray-900 dark:text-slate-100">
                        {value}
                    </p>
                </div>
                <div className={`inline-flex h-11 w-11 items-center justify-center rounded-xl ring-1 ${toneStyles[tone]}`}>
                    <Icon className="h-5 w-5" aria-hidden="true" />
                </div>
            </div>
            {(trend !== null && trend !== undefined) && (
                <div className="mt-4 flex items-center justify-between text-sm">
                    <span className={`font-medium ${trendColor}`}>
                        {trend > 0 ? '+' : ''}{trend}
                    </span>
                    {comparisonLabel && (
                        <span className="text-gray-500 dark:text-slate-400">{comparisonLabel}</span>
                    )}
                </div>
            )}
        </div>
    );
}
