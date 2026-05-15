import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { LocaleProvider } from '@/contexts/LocaleContext';
import ConfirmProvider from '@/Components/ConfirmProvider';
import { initTheme, type ThemePreference } from '@/lib/theme';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.tsx`,
            import.meta.glob('./Pages/**/*.tsx'),
        ),
    setup({ el, App, props }) {
        const initialProps = props.initialPage.props as Record<string, unknown>;
        const settings = (initialProps.settings as Record<string, unknown> | undefined) ?? {};
        const defaultLocale = ((initialProps.locale as string | undefined) ?? 'en') as 'en' | 'am';

        initTheme(
            (settings['appearance.default_theme'] as ThemePreference | undefined) ?? 'system',
            Boolean(settings['appearance.allow_user_theme_switching'] ?? true),
        );

        const root = createRoot(el);
        root.render(
            <LocaleProvider defaultLocale={defaultLocale}>
                <ConfirmProvider>
                    <App {...props} />
                </ConfirmProvider>
            </LocaleProvider>,
        );
    },
    progress: {
        color: '#2563eb',
    },
});
