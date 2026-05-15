import { ReactNode } from 'react';

interface Props {
    title: string;
    description?: string;
    actions?: ReactNode;
}

export default function PageHeader({ title, description, actions }: Props) {
    return (
        <div className="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 className="text-xl font-semibold text-gray-900 dark:text-slate-100">
                    {title}
                </h1>
                {description && (
                    <p className="mt-1 text-sm text-gray-500 dark:text-slate-400">
                        {description}
                    </p>
                )}
            </div>
            {actions && (
                <div className="flex shrink-0 items-center gap-2 sm:ml-4">{actions}</div>
            )}
        </div>
    );
}
