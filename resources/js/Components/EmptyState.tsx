import { ReactNode } from 'react';
import { Inbox } from '@/Components/Icons';

interface Props {
    title?: string;
    description?: string;
    action?: ReactNode;
    icon?: ReactNode;
}

export default function EmptyState({
    title = 'No results found',
    description,
    action,
    icon,
}: Props) {
    return (
        <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-900">
            <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-slate-800 dark:text-slate-500">
                {icon ?? <Inbox className="h-6 w-6" />}
            </div>
            <h3 className="mt-4 text-sm font-semibold text-gray-900 dark:text-slate-100">
                {title}
            </h3>
            {description && (
                <p className="mt-1 text-sm text-gray-500 dark:text-slate-400">{description}</p>
            )}
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}
