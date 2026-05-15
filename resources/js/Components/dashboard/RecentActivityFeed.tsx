import EmptyDashboardState from './EmptyDashboardState';

interface ActivityItem {
    id: string;
    event: string;
    actor: string;
    subject: string;
    timestamp: string | null;
    severity: 'info' | 'warning' | 'critical';
}

interface Props {
    items: ActivityItem[];
    t: (key: string) => string;
}

export default function RecentActivityFeed({ items, t }: Props) {
    if (items.length === 0) {
        return <EmptyDashboardState title={t('dashboard.noRecentActivity')} compact />;
    }

    return (
        <div className="space-y-3">
            {items.map((item) => (
                <div key={item.id} className="rounded-2xl border border-gray-200 px-4 py-3 dark:border-slate-800">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <p className="text-sm font-medium text-gray-900 dark:text-slate-100">
                                {t(`dashboard.auditEvents.${item.event}`)}
                            </p>
                            <p className="mt-1 text-xs text-gray-500 dark:text-slate-400">
                                {item.subject || item.actor}
                            </p>
                        </div>
                        <span className="text-xs text-gray-500 dark:text-slate-400">
                            {item.timestamp ? new Date(item.timestamp).toLocaleString() : '—'}
                        </span>
                    </div>
                </div>
            ))}
        </div>
    );
}
