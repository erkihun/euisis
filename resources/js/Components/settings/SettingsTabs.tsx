import { useLocale } from '@/hooks/useLocale';

type Tab = {
    id: string;
    labelKey: string;
    disabled?: boolean;
};

type Props = {
    tabs: Tab[];
    activeTab: string;
    onSelect: (tabId: string) => void;
};

export default function SettingsTabs({ tabs, activeTab, onSelect }: Props) {
    const { t } = useLocale();

    return (
        <nav
            aria-label={t('settings.title')}
            className="flex w-full overflow-x-auto border-b border-gray-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-t-2xl"
        >
            {tabs.map((tab) => {
                const active = tab.id === activeTab;

                return (
                    <button
                        key={tab.id}
                        type="button"
                        onClick={() => onSelect(tab.id)}
                        disabled={tab.disabled}
                        className={[
                            'shrink-0 whitespace-nowrap px-5 py-3 text-sm font-medium border-b-2 transition-colors',
                            active
                                ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:border-slate-600',
                            tab.disabled ? 'cursor-not-allowed opacity-50' : '',
                        ].join(' ')}
                        aria-current={active ? 'page' : undefined}
                    >
                        {t(tab.labelKey)}
                    </button>
                );
            })}
        </nav>
    );
}
