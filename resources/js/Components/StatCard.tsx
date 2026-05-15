import { ReactNode } from 'react';

interface Props {
    label: string;
    value: string | number;
    detail?: ReactNode;
    accent?: boolean;
    icon?: ReactNode;
}

export default function StatCard({ label, value, detail, accent = false, icon }: Props) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
            <div className="flex items-start justify-between">
                <p className="text-sm font-medium text-gray-500 dark:text-slate-400">{label}</p>
                {icon && (
                    <div className={[
                        'flex h-8 w-8 items-center justify-center rounded-lg',
                        accent
                            ? 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400'
                            : 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
                    ].join(' ')}>
                        {icon}
                    </div>
                )}
            </div>
            <p
                className={[
                    'mt-2 text-2xl font-bold tabular-nums',
                    accent
                        ? 'text-orange-600 dark:text-orange-400'
                        : 'text-blue-600 dark:text-blue-400',
                ].join(' ')}
            >
                {value}
            </p>
            {detail && (
                <div className="mt-2 space-y-0.5 text-xs text-gray-400 dark:text-slate-500">
                    {detail}
                </div>
            )}
        </div>
    );
}
