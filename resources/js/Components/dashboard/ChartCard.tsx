import type { ReactNode } from 'react';

interface Props {
    title: string;
    description?: string;
    children: ReactNode;
    footer?: ReactNode;
}

export default function ChartCard({ title, description, children, footer }: Props) {
    return (
        <div className="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div className="mb-4">
                <h3 className="text-sm font-semibold text-gray-900 dark:text-slate-100">
                    {title}
                </h3>
                {description && (
                    <p className="mt-1 text-sm text-gray-500 dark:text-slate-400">
                        {description}
                    </p>
                )}
            </div>
            <div className="min-h-[260px]">{children}</div>
            {footer && <div className="mt-4 border-t border-gray-100 pt-4 dark:border-slate-800">{footer}</div>}
        </div>
    );
}
