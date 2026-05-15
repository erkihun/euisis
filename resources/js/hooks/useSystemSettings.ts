import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import {
    getBooleanSetting,
    getNumberSetting,
    getSetting,
    getStringArraySetting,
    getStringSetting,
    type PublicSettings,
} from '@/lib/settings';

export function useSystemSettings() {
    const page = usePage<PageProps<{ settings?: PublicSettings }>>();
    const settings = page.props.settings ?? {};

    return {
        settings,
        get: <T,>(key: string, fallback: T): T => getSetting(settings, key, fallback),
        getString: (key: string, fallback = ''): string => getStringSetting(settings, key, fallback),
        getBoolean: (key: string, fallback = false): boolean => getBooleanSetting(settings, key, fallback),
        getNumber: (key: string, fallback = 0): number => getNumberSetting(settings, key, fallback),
        getStringArray: (key: string, fallback: string[] = []): string[] => getStringArraySetting(settings, key, fallback),
    };
}
