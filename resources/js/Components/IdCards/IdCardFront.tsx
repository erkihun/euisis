import type { CSSProperties } from 'react';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';

type IdCardFrontProps = {
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
    /** When supplied and not 'active', a diagonal watermark is shown */
    status?: string | null;
    /** Show the city/system logo in header */
    cityLogoUrl?: string | null;
    /** Extra styles merged onto the root div — use to force explicit height for html-to-image export */
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

export default function IdCardFront({
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
}: IdCardFrontProps) {
    const { t, locale } = useLocale();
    const { getString, getBoolean } = useSystemSettings();

    const frontFrom  = getString('id_cards.front_bg_from', '#1D4ED8');
    const frontTo    = getString('id_cards.front_bg_to', '#1E3A8A');
    const textPri    = getString('id_cards.front_text_primary', '#FFFFFF');
    const textSec    = getString('id_cards.front_text_secondary', '#BFDBFE');
    const nameFontSz = getString('id_cards.front_name_font_size', 'sm');
    const lblFontSz  = getString('id_cards.front_label_font_size', 'xs');
    const showLogo   = getBoolean('id_cards.show_organization_logo', true);
    const padding    = getString('id_cards.card_padding', 'normal');

    const cityName = locale === 'am'
        ? getString('id_cards.city_name_am', 'አዲስ አበባ ከተማ አስተዳደር')
        : getString('id_cards.city_name_en', 'Addis Ababa City Administration');
    const bureauName = locale === 'am'
        ? getString('id_cards.bureau_name_am', 'የሲቪል ሰርቪስና ሰው ሃብት ልማት ቢሮ')
        : getString('id_cards.bureau_name_en', 'Public Service & HRD Bureau');

    const nameSizeMap: Record<string, string> = { xs: 'text-xs', sm: 'text-sm', base: 'text-base', lg: 'text-lg' };
    const lblSizeMap: Record<string, string>  = { xs: 'text-[9px]', sm: 'text-xs' };
    const padCls = padding === 'compact' ? 'px-3 pb-2' : padding === 'spacious' ? 'px-5 pb-5' : 'px-4 pb-3';

    const nameCls = nameSizeMap[nameFontSz] ?? 'text-sm';
    const lblCls  = lblSizeMap[lblFontSz]  ?? 'text-[9px]';

    const watermarkText = status ? WATERMARK_STATUSES[status] : null;
    const displayName = locale === 'am' && fullNameAm ? fullNameAm : fullName;

    return (
        <div
            className="relative overflow-hidden rounded-xl shadow-xl"
            style={{
                aspectRatio: '85.6/54',
                width: '100%',
                maxWidth: 400,
                background: `linear-gradient(135deg, ${frontFrom} 0%, ${frontTo} 100%)`,
                ...rootStyle,
            }}
        >
            {/* Security dot pattern overlay */}
            <div
                className="absolute inset-0 pointer-events-none"
                style={{
                    backgroundImage: `radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px)`,
                    backgroundSize: '12px 12px',
                }}
            />

            {/* "EMPLOYEE ID" watermark text in background */}
            <div
                className="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden"
                aria-hidden
            >
                <span
                    className="text-white font-black tracking-[0.4em] whitespace-nowrap"
                    style={{
                        fontSize: '2rem',
                        opacity: 0.04,
                        transform: 'rotate(-20deg)',
                        userSelect: 'none',
                    }}
                >
                    EMPLOYEE ID
                </span>
            </div>

            {/* Status watermark (EXPIRED / REVOKED / LOST / SUSPENDED) */}
            {watermarkText && (
                <div
                    className="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden"
                    aria-hidden
                >
                    <span
                        className="font-black tracking-widest"
                        style={{
                            fontSize: '1.6rem',
                            color: '#FF0000',
                            opacity: 0.18,
                            transform: 'rotate(-30deg)',
                            userSelect: 'none',
                            whiteSpace: 'nowrap',
                        }}
                    >
                        {watermarkText}
                    </span>
                </div>
            )}

            {/* Header band */}
            <div className="absolute inset-x-0 top-0 flex items-center gap-2 bg-white/10 px-3 py-1.5 backdrop-blur-[1px]">
                {/* City logo or org logo */}
                {showLogo && (cityLogoUrl || organizationLogoUrl) ? (
                    <img
                        src={cityLogoUrl ?? organizationLogoUrl!}
                        alt={organizationName ?? 'Logo'}
                        className="h-7 w-7 rounded-full object-contain bg-white/10"
                        crossOrigin="anonymous"
                    />
                ) : (
                    <div
                        className="h-7 w-7 rounded-full bg-white/20 flex items-center justify-center text-[9px] font-bold shrink-0"
                        style={{ color: textPri }}
                    >
                        AA
                    </div>
                )}
                <div className="min-w-0 flex-1">
                    <p className="truncate text-[10px] font-bold leading-tight" style={{ color: textPri }}>{cityName}</p>
                    <p className="truncate text-[8px] leading-tight" style={{ color: textSec }}>{bureauName}</p>
                </div>
                <div className="ml-auto shrink-0">
                    <span
                        className="rounded px-1.5 py-0.5 text-[8px] font-mono uppercase tracking-wider bg-white/20 border border-white/20"
                        style={{ color: textPri }}
                    >
                        {t('idCards.officialIdBadge')}
                    </span>
                </div>
            </div>

            {/* Body */}
            <div className={`absolute inset-x-0 top-12 bottom-6 flex gap-3 ${padCls} pt-1`}>
                {/* Photo column */}
                <div className="flex-shrink-0 flex flex-col items-start gap-1">
                    {photoUrl ? (
                        <img
                            src={photoUrl}
                            alt={t('employees.photo')}
                            crossOrigin="anonymous"
                            className="rounded-lg object-cover"
                            style={{
                                width: '4.5rem',
                                height: '6rem',
                                border: '1px solid rgba(255,255,255,0.2)',
                            }}
                        />
                    ) : (
                        <div
                            className="rounded-lg bg-white/15 flex items-center justify-center text-[7px] text-center leading-tight"
                            style={{
                                width: '4.5rem',
                                height: '6rem',
                                color: textSec,
                                border: '1px solid rgba(255,255,255,0.15)',
                            }}
                        >
                            {t('idCards.photoPlaceholder')}
                        </div>
                    )}
                    {/* Employee / Card numbers below photo */}
                    <div className="w-full space-y-0.5">
                        <div>
                            <span className={`${lblCls} uppercase tracking-wider block`} style={{ color: textSec }}>
                                {t('idCards.idLabel')}
                            </span>
                            <span className={`${lblCls} font-mono truncate block font-medium`} style={{ color: textPri }}>
                                {employeeNumber ?? '—'}
                            </span>
                        </div>
                        <div>
                            <span className={`${lblCls} uppercase tracking-wider block`} style={{ color: textSec }}>
                                {t('idCards.cardLabel')}
                            </span>
                            <span className={`${lblCls} font-mono truncate block`} style={{ color: textPri }}>
                                {cardNumber}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Text fields */}
                <div className="min-w-0 flex-1 flex flex-col justify-between">
                    <div className="space-y-0.5">
                        <p className={`${nameCls} font-bold leading-tight`} style={{ color: textPri }}>
                            {displayName ?? '—'}
                        </p>
                        {positionTitle && (
                            <p className="text-[10px] leading-tight truncate" style={{ color: textSec }}>
                                {positionTitle}
                            </p>
                        )}
                        {organizationName && (
                            <p className="text-[9px] leading-tight truncate mt-0.5" style={{ color: textSec, opacity: 0.8 }}>
                                {organizationName}
                            </p>
                        )}
                    </div>
                    {/* Issue / Expiry date pills */}
                    <div className="flex gap-1.5">
                        {issueDate && (
                            <div className="flex-1 rounded-md px-1.5 py-1 bg-white/10 border border-white/10">
                                <p className="text-[7px] uppercase tracking-wider leading-none mb-0.5" style={{ color: textSec }}>
                                    {t('idCards.issueDate')}
                                </p>
                                <p className={`${lblCls} font-semibold font-mono leading-none`} style={{ color: textPri }}>
                                    {issueDate}
                                </p>
                            </div>
                        )}
                        {expiryDate && (
                            <div className="flex-1 rounded-md px-1.5 py-1 bg-white/10 border border-white/10">
                                <p className="text-[7px] uppercase tracking-wider leading-none mb-0.5" style={{ color: textSec }}>
                                    {t('idCards.expLabel')}
                                </p>
                                <p className={`${lblCls} font-semibold font-mono leading-none`} style={{ color: textPri }}>
                                    {expiryDate}
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Bottom accent bar */}
            <div
                className="absolute inset-x-0 bottom-0 h-5 flex items-center px-4"
                style={{
                    background: `linear-gradient(to right, rgba(255,255,255,0.12), rgba(255,255,255,0.06))`,
                    borderTop: '1px solid rgba(255,255,255,0.1)',
                }}
            >
                <span className="text-[7px] font-mono tracking-widest uppercase" style={{ color: textSec, opacity: 0.7 }}>
                    {t('idCards.authorizedLabel')}
                </span>
            </div>

            {/* Decorative diagonal accent (right edge) */}
            <div className="absolute -right-4 top-0 bottom-0 w-12 bg-white/5 -skew-x-12 pointer-events-none" />
            <div className="absolute -right-8 top-0 bottom-0 w-8 bg-white/3 -skew-x-12 pointer-events-none" />
        </div>
    );
}
