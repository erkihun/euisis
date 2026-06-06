import { useRef, useState, useEffect } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { MoreVertical, PencilIcon, TrashIcon, Plus } from '@/Components/Icons';
import { useLocale } from '@/hooks/useLocale';

type Props = {
    organizationId: string;
    can: {
        update: boolean;
        delete: boolean;
        createChild: boolean;
    };
};

export default function OrganizationActionsMenu({ organizationId, can }: Props) {
    const { t } = useLocale();
    const [open, setOpen] = useState(false);
    const ref = useRef<HTMLDivElement>(null);
    const { delete: destroy, processing } = useForm();

    useEffect(() => {
        if (!open) return;
        function handler(e: MouseEvent) {
            if (ref.current && !ref.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, [open]);

    const hasAny = can.update || can.delete || can.createChild;
    if (!hasAny) return null;

    function handleDelete() {
        if (!confirm(t('organizations.deleteConfirm'))) return;
        destroy(route('organizations.archive', organizationId), {
            onSuccess: () => setOpen(false),
        });
    }

    return (
        <div ref={ref} className="relative">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                className="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-slate-500 dark:hover:bg-slate-700 dark:hover:text-slate-300"
                aria-label={t('common.actions')}
            >
                <MoreVertical className="h-4 w-4" />
            </button>

            {open && (
                <div className="absolute right-0 z-20 mt-1 w-40 rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                    {can.createChild && (
                        <Link
                            href={route('organizations.create') + `?parent=${organizationId}`}
                            className="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700"
                            onClick={() => setOpen(false)}
                        >
                            <Plus className="h-3.5 w-3.5" />
                            {t('organizations.createChild')}
                        </Link>
                    )}
                    {can.update && (
                        <Link
                            href={route('organizations.edit', organizationId)}
                            className="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-slate-200 dark:hover:bg-slate-700"
                            onClick={() => setOpen(false)}
                        >
                            <PencilIcon className="h-3.5 w-3.5" />
                            {t('common.edit')}
                        </Link>
                    )}
                    {can.delete && (
                        <button
                            type="button"
                            disabled={processing}
                            onClick={handleDelete}
                            className="flex w-full items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                        >
                            <TrashIcon className="h-3.5 w-3.5" />
                            {t('common.delete')}
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
