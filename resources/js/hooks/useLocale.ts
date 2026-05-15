import { usePage } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import { useLocaleContext } from '@/contexts/LocaleContext';
import am from '@/i18n/am';
import amCommon from '@/i18n/am/common';
import amConfirmations from '@/i18n/am/confirmations';
import amCodeRules from '@/i18n/am/codeRules';
import amDashboard from '@/i18n/am/dashboard';
import amEntitlementRules from '@/i18n/am/entitlementRules';
import amHome from '@/i18n/am/home';
import amHierarchyVersions from '@/i18n/am/hierarchyVersions';
import amNavigation from '@/i18n/am/navigation';
import amOrganizations from '@/i18n/am/organizations';
import amPositions from '@/i18n/am/positions';
import amProfile from '@/i18n/am/profile';
import amRecycleBin from '@/i18n/am/recycleBin';
import amServiceTypes from '@/i18n/am/serviceTypes';
import amSettings from '@/i18n/am/settings';
import amOrganizationUnits from '@/i18n/am/organizationUnits';
import amOrganizationUnitTypes from '@/i18n/am/organizationUnitTypes';
import amTransfers from '@/i18n/am/transfers';
import amUsers from '@/i18n/am/users';
import en from '@/i18n/en';
import enCommon from '@/i18n/en/common';
import enConfirmations from '@/i18n/en/confirmations';
import enCodeRules from '@/i18n/en/codeRules';
import enDashboard from '@/i18n/en/dashboard';
import enEntitlementRules from '@/i18n/en/entitlementRules';
import enHome from '@/i18n/en/home';
import enHierarchyVersions from '@/i18n/en/hierarchyVersions';
import enNavigation from '@/i18n/en/navigation';
import enOrganizationUnits from '@/i18n/en/organizationUnits';
import enOrganizationUnitTypes from '@/i18n/en/organizationUnitTypes';
import enOrganizations from '@/i18n/en/organizations';
import enPositions from '@/i18n/en/positions';
import enProfile from '@/i18n/en/profile';
import enRecycleBin from '@/i18n/en/recycleBin';
import enServiceTypes from '@/i18n/en/serviceTypes';
import enSettings from '@/i18n/en/settings';
import enTransfers from '@/i18n/en/transfers';
import enUsers from '@/i18n/en/users';
import type { PageProps } from '@/types';

type Locale = 'en' | 'am';
type TranslationTree = Record<string, unknown>;

const translations: Record<Locale, TranslationTree> = {
    en: {
        ...en,
        common: { ...((en.common as TranslationTree | undefined) ?? {}), ...enCommon },
        nav: { ...en.nav, ...enNavigation },
        dashboard: { ...((en.dashboard as TranslationTree | undefined) ?? {}), ...enDashboard },
        organizations: { ...((en.organizations as TranslationTree | undefined) ?? {}), ...enOrganizations },
        organizationUnits: enOrganizationUnits,
        organizationUnitTypes: enOrganizationUnitTypes,
        hierarchyVersions: enHierarchyVersions,
        transfers: enTransfers,
        positions: enPositions,
        profile: enProfile,
        recycleBin: enRecycleBin,
        serviceTypes: enServiceTypes,
        entitlementRules: enEntitlementRules,
        codeRules: enCodeRules,
        settings: enSettings,
        home: enHome,
        users: { ...((en.users as TranslationTree | undefined) ?? {}), ...enUsers },
        confirmations: enConfirmations,
    },
    am: {
        ...(am as TranslationTree),
        common: { ...(((am as { common?: TranslationTree }).common) ?? {}), ...amCommon },
        nav: { ...(((am as { nav?: TranslationTree }).nav) ?? {}), ...amNavigation },
        dashboard: { ...(((am as { dashboard?: TranslationTree }).dashboard) ?? {}), ...amDashboard },
        organizations: { ...(((am as { organizations?: TranslationTree }).organizations) ?? {}), ...amOrganizations },
        organizationUnits: amOrganizationUnits,
        organizationUnitTypes: amOrganizationUnitTypes,
        hierarchyVersions: amHierarchyVersions,
        transfers: amTransfers,
        positions: amPositions,
        profile: amProfile,
        recycleBin: amRecycleBin,
        serviceTypes: amServiceTypes,
        entitlementRules: amEntitlementRules,
        codeRules: amCodeRules,
        settings: amSettings,
        home: amHome,
        users: { ...(((am as { users?: TranslationTree }).users) ?? {}), ...amUsers },
        confirmations: amConfirmations,
    },
};

function getNestedValue(tree: TranslationTree, path: string): string {
    const segments = path.split('.');
    let current: unknown = tree;

    for (const segment of segments) {
        if (current === null || typeof current !== 'object') {
            return path;
        }

        current = (current as TranslationTree)[segment];
    }

    return typeof current === 'string' ? current : path;
}

export function useLocale() {
    const { locale, setLocale } = useLocaleContext();
    const page = usePage<PageProps<{ locale?: string; settings?: Record<string, unknown> }>>();
    const supportedLocales = ((page.props.settings?.['localization.supported_locales'] as string[] | undefined) ?? ['en', 'am'])
        .filter((code): code is Locale => code === 'en' || code === 'am');

    const t = useCallback(
        (key: string): string => getNestedValue(translations[locale] ?? translations.en, key),
        [locale],
    );

    const localeOptions = useMemo(
        () => supportedLocales.map((code) => ({ value: code, label: code === 'am' ? 'አማ' : 'EN' })),
        [supportedLocales],
    );

    return { locale, setLocale, t, localeOptions };
}
