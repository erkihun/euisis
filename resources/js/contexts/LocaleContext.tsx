import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react';

type Locale = 'en' | 'am';

type LocaleContextValue = {
    locale: Locale;
    setLocale: (l: Locale) => void;
};

const STORAGE_KEY = 'euisis_locale';

function getInitialLocale(): Locale {
    try {
        const stored = localStorage.getItem(STORAGE_KEY) as Locale | null;
        if (stored === 'en' || stored === 'am') return stored;
    } catch {
        // localStorage unavailable (SSR / private mode)
    }
    return 'am';
}

export const LocaleContext = createContext<LocaleContextValue>({
    locale: 'am',
    setLocale: () => {},
});

type LocaleProviderProps = {
    children: ReactNode;
    defaultLocale?: Locale;
};

export function LocaleProvider({ children, defaultLocale = 'am' }: LocaleProviderProps) {
    const [locale, setLocaleState] = useState<Locale>(() => getInitialLocale() ?? defaultLocale);

    const setLocale = useCallback((next: Locale) => {
        try { localStorage.setItem(STORAGE_KEY, next); } catch { /* ignore */ }
        setLocaleState(next);
    }, []);

    useEffect(() => {
        document.documentElement.lang = locale;
        document.body.classList.toggle('locale-am', locale === 'am');
    }, [locale]);

    return (
        <LocaleContext.Provider value={{ locale, setLocale }}>
            {children}
        </LocaleContext.Provider>
    );
}

export function useLocaleContext(): LocaleContextValue {
    return useContext(LocaleContext);
}
