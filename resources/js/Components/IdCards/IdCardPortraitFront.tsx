import type { CSSProperties } from 'react';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';

type Props = {
    cardNumber: string;
    fullName?: string | null;
    fullNameAm?: string | null;
    employeeNumber?: string | null;
    organizationName?: string | null;
    organizationLogoUrl?: string | null;
    positionTitle?: string | null;
    photoUrl?: string | null;
    issueDate?: string | null;
    expiryDate?: string | null;
    status?: string | null;
    cityLogoUrl?: string | null;
    rootStyle?: CSSProperties;
};

const WATERMARK_STATUSES: Record<string, string> = {
    expired:   'EXPIRED',
    revoked:   'REVOKED',
    lost:      'LOST',
    suspended: 'SUSPENDED',
    replaced:  'REPLACED',
    damaged:   'DAMAGED',
};

export default function IdCardPortraitFront({
    cardNumber,
    fullName,
    fullNameAm,
    employeeNumber,
    organizationName,
    organizationLogoUrl,
    positionTitle,
    photoUrl,
    issueDate,
    expiryDate,
    status,
    cityLogoUrl,
    rootStyle,
}: Props) {
    const { t, locale } = useLocale();
    const { getString, getBoolean } = useSystemSettings();

    const frontFrom = getString('id_cards.front_bg_from', '#1D4ED8');
    const frontTo   = getString('id_cards.front_bg_to',   '#1E3A8A');
    const textPri   = getString('id_cards.front_text_primary',   '#FFFFFF');
    const textSec   = getString('id_cards.front_text_secondary', '#BFDBFE');
    const showLogo         = getBoolean('id_cards.show_organization_logo', true);
    const systemLogoUrl    = getString('general.identity_system_logo_url', '');
    const resolvedCityLogo = cityLogoUrl || (systemLogoUrl || null);

    const cityName = locale === 'am'
        ? getString('id_cards.city_name_am', 'አዲስ አበባ ከተማ አስተዳደር')
        : getString('id_cards.city_name_en', 'Addis Ababa City Administration');
    const bureauName = locale === 'am'
        ? getString('id_cards.bureau_name_am', 'የሲቪል ሰርቪስና ሰው ሃብት ልማት ቢሮ')
        : getString('id_cards.bureau_name_en', 'Public Service & HRD Bureau');

    const watermarkText = status ? WATERMARK_STATUSES[status] : null;
    const displayName   = locale === 'am' && fullNameAm ? fullNameAm : fullName;

    return (
        <div
            className="relative flex flex-col overflow-hidden rounded-2xl shadow-xl"
            style={{
                aspectRatio: '54 / 85.6',
                width: '100%',
                maxWidth: 260,
                background: `linear-gradient(160deg, ${frontFrom} 0%, ${frontTo} 100%)`,
                ...rootStyle,
            }}
        >
            {/* Security dot pattern */}
            <div
                className="pointer-events-none absolute inset-0"
                style={{
                    backgroundImage: `radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px)`,
                    backgroundSize: '10px 10px',
                }}
            />

            {/* Diagonal decorative accent strips */}
            <div className="pointer-events-none absolute -right-3 bottom-0 top-0 w-10 -skew-x-6 bg-white/[0.04]" />
            <div className="pointer-events-none absolute -right-6 bottom-0 top-0 w-7  -skew-x-6 bg-white/[0.025]" />

            {/* Background "EMPLOYEE ID" watermark */}
            <div
                className="pointer-events-none absolute inset-0 flex select-none items-center justify-center overflow-hidden"
                aria-hidden
            >
                <span
                    className="whitespace-nowrap font-black text-white"
                    style={{ fontSize: '1.3rem', opacity: 0.04, transform: 'rotate(-35deg)', letterSpacing: '0.35em' }}
                >
                    EMPLOYEE ID
                </span>
            </div>

            {/* Status watermark */}
            {watermarkText && (
                <div
                    className="pointer-events-none absolute inset-0 flex select-none items-center justify-center overflow-hidden"
                    aria-hidden
                >
                    <span
                        className="font-black tracking-widest"
                        style={{ fontSize: '1.3rem', color: '#FF0000', opacity: 0.18, transform: 'rotate(-30deg)', whiteSpace: 'nowrap' }}
                    >
                        {watermarkText}
                    </span>
                </div>
            )}

            {/* ── Header ───────────────────────────────────────────── */}
            <div className="flex shrink-0 items-center gap-2 bg-white/15 px-3 py-2">
                {showLogo && (resolvedCityLogo || organizationLogoUrl) ? (
                    <img
                        src={resolvedCityLogo ?? organizationLogoUrl!}
                        alt={organizationName ?? 'Logo'}
                        className="h-7 w-7 shrink-0 rounded-full object-contain bg-white/10"
                        crossOrigin="anonymous"
                    />
                ) : (
                    <div
                        className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white/20 text-[8px] font-bold"
                        style={{ color: textPri }}
                    >
                        AA
                    </div>
                )}
                <div className="min-w-0 flex-1">
                    <p className="truncate text-[9px] font-bold leading-tight" style={{ color: textPri }}>{cityName}</p>
                    <p className="truncate text-[7px] leading-tight" style={{ color: textSec }}>{bureauName}</p>
                </div>
                <span
                    className="shrink-0 rounded border border-white/20 bg-white/20 px-1.5 py-0.5 text-[7px] font-mono uppercase tracking-wide"
                    style={{ color: textPri }}
                >
                    {t('idCards.officialIdBadge')}
                </span>
            </div>

            {/* ── Body ─────────────────────────────────────────────── */}
            <div className="flex flex-1 flex-col items-center justify-between px-3 py-3">

                {/* Photo */}
                <div className="flex flex-col items-center gap-2">
                    {photoUrl ? (
                        <img
                            src={photoUrl}
                            alt={t('employees.photo')}
                            crossOrigin="anonymous"
                            className="rounded-xl object-cover"
                            style={{
                                width: '6rem',
                                height: '7.5rem',
                                border: '2px solid rgba(255,255,255,0.25)',
                                boxShadow: '0 4px 16px rgba(0,0,0,0.3)',
                            }}
                        />
                    ) : (
                        <div
                            className="flex items-center justify-center rounded-xl bg-white/15 text-center text-[7px] leading-tight"
                            style={{
                                width: '6rem',
                                height: '7.5rem',
                                color: textSec,
                                border: '2px solid rgba(255,255,255,0.15)',
                            }}
                        >
                            {t('idCards.photoPlaceholder')}
                        </div>
                    )}
                </div>

                {/* Name / Position / Org */}
                <div className="w-full space-y-0.5 text-center">
                    <p className="text-[12px] font-bold leading-snug" style={{ color: textPri }}>
                        {displayName ?? '—'}
                    </p>
                    {positionTitle && (
                        <p className="truncate text-[9px] leading-tight" style={{ color: textSec }}>
                            {positionTitle}
                        </p>
                    )}
                    {organizationName && (
                        <p className="truncate text-[8px] leading-tight" style={{ color: textSec, opacity: 0.8 }}>
                            {organizationName}
                        </p>
                    )}
                </div>

                {/* Thin divider */}
                <div className="w-full h-px" style={{ background: 'rgba(255,255,255,0.15)' }} />

                {/* Employee ID + Card Number */}
                <div className="flex w-full justify-around gap-2">
                    <div className="text-center">
                        <span className="block text-[7px] uppercase tracking-wider" style={{ color: textSec }}>
                            {t('idCards.idLabel')}
                        </span>
                        <span className="block text-[9px] font-mono font-semibold" style={{ color: textPri }}>
                            {employeeNumber ?? '—'}
                        </span>
                    </div>
                    <div className="w-px" style={{ background: 'rgba(255,255,255,0.12)' }} />
                    <div className="text-center">
                        <span className="block text-[7px] uppercase tracking-wider" style={{ color: textSec }}>
                            {t('idCards.cardLabel')}
                        </span>
                        <span className="block text-[9px] font-mono" style={{ color: textPri }}>
                            {cardNumber}
                        </span>
                    </div>
                </div>

                {/* Issue / Expiry date pills */}
                <div className="flex w-full gap-2">
                    {issueDate && (
                        <div className="flex-1 rounded-lg border border-white/10 bg-white/10 px-2 py-1.5 text-center">
                            <p className="text-[6px] uppercase leading-none mb-0.5" style={{ color: textSec }}>
                                {t('idCards.issueDate')}
                            </p>
                            <p className="text-[8px] font-semibold font-mono leading-none" style={{ color: textPri }}>
                                {issueDate}
                            </p>
                        </div>
                    )}
                    {expiryDate && (
                        <div className="flex-1 rounded-lg border border-white/10 bg-white/10 px-2 py-1.5 text-center">
                            <p className="text-[6px] uppercase leading-none mb-0.5" style={{ color: textSec }}>
                                {t('idCards.expLabel')}
                            </p>
                            <p className="text-[8px] font-semibold font-mono leading-none" style={{ color: textPri }}>
                                {expiryDate}
                            </p>
                        </div>
                    )}
                </div>
            </div>

            {/* ── Bottom accent bar ─────────────────────────────────── */}
            <div
                className="flex h-5 shrink-0 items-center px-4"
                style={{
                    background: `linear-gradient(to right, rgba(255,255,255,0.12), rgba(255,255,255,0.06))`,
                    borderTop: '1px solid rgba(255,255,255,0.1)',
                }}
            >
                <span className="text-[6px] font-mono uppercase tracking-widest" style={{ color: textSec, opacity: 0.7 }}>
                    {t('idCards.authorizedLabel')}
                </span>
            </div>
        </div>
    );
}
