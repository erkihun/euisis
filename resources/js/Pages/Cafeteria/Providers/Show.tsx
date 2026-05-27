import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PageHeader from '@/Components/PageHeader';
import StatusBadge from '@/Components/StatusBadge';
import { Head, Link } from '@inertiajs/react';
import { useLocale } from '@/hooks/useLocale';

type Provider = {
    id: string; code: string; name_en: string; name_am: string | null;
    contact_person: string | null; phone_number: string | null; email: string | null;
    location: string | null; is_active: boolean; created_at: string;
    can: { update: boolean; archive: boolean };
};

export default function ProviderShow({ provider }: { provider: Provider }) {
    const { t } = useLocale();
    const rowCls = 'flex justify-between border-b border-gray-100 py-3 text-sm last:border-0 dark:border-slate-800';

    return (
        <AuthenticatedLayout
            header={
                <PageHeader
                    title={provider.name_en}
                    actions={
                        provider.can.update ? (
                            <Link href={route('cafeteria.providers.edit', provider.id)} className="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                {t('common.edit')}
                            </Link>
                        ) : undefined
                    }
                />
            }
        >
            <Head title={provider.name_en} />
            <div className="mx-auto max-w-2xl">
                <div className="rounded-xl border border-gray-200 bg-white px-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.providerCode')}</span><span className="font-mono font-medium text-gray-900 dark:text-white">{provider.code}</span></div>
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.nameEn')}</span><span className="font-medium text-gray-900 dark:text-white">{provider.name_en}</span></div>
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.nameAm')}</span><span className="text-gray-700 dark:text-slate-300">{provider.name_am ?? '—'}</span></div>
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.contactPerson')}</span><span className="text-gray-700 dark:text-slate-300">{provider.contact_person ?? '—'}</span></div>
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.phoneNumber')}</span><span className="text-gray-700 dark:text-slate-300">{provider.phone_number ?? '—'}</span></div>
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.location')}</span><span className="text-gray-700 dark:text-slate-300">{provider.location ?? '—'}</span></div>
                    <div className={rowCls}><span className="text-gray-500">{t('cafeteria.isActive')}</span><StatusBadge status={provider.is_active ? 'active' : 'inactive'} label={provider.is_active ? t('common.active') : t('common.inactive')} /></div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
