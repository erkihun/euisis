import type { CSSProperties } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';

type Props = {
    cardNumber: string;
    qrValue?: string | null;
    rootStyle?: CSSProperties;
};

export default function IdCardPortraitBack({ cardNumber, qrValue, rootStyle }: Props) {
    const { t, locale } = useLocale();
    const { getString, getBoolean } = useSystemSettings();

    const backFrom        = getString('id_cards.back_bg_from', '#1E293B');
    const backTo          = getString('id_cards.back_bg_to',   '#0F172A');
    const textColor       = getString('id_cards.back_text_color', '#94A3B8');
    const showMagStripe   = getBoolean('id_cards.show_magnetic_stripe', true);
    const verificationUrl = getString('id_cards.verification_url', '');
    const supportContact  = getString('id_cards.support_contact', '');

    const returnAddress = locale === 'am'
        ? getString('id_cards.return_address_am', 'አዲስ አበባ ከተማ አስተዳደር፣ የሲቪል ሰርቪስና ሰው ሃብት ልማት ቢሮ')
        : getString('id_cards.return_address_en', 'Addis Ababa City Administration, Public Service & HRD Bureau');

    const scanLabel = locale === 'am' ? 'ለማረጋገጥ ስካን ያድርጉ' : t('idCards.scanToVerify');

    return (
        <div
            className="relative flex flex-col overflow-hidden rounded-2xl shadow-xl"
            style={{
                aspectRatio: '54 / 85.6',
                width: '100%',
                maxWidth: 260,
                background: `linear-gradient(160deg, ${backFrom} 0%, ${backTo} 100%)`,
                ...rootStyle,
            }}
        >
            {/* Security dot pattern */}
            <div
                className="pointer-events-none absolute inset-0"
                style={{
                    backgroundImage: `radial-gradient(circle, rgba(255,255,255,0.035) 1px, transparent 1px)`,
                    backgroundSize: '10px 10px',
                }}
            />

            {/* Magnetic stripe */}
            {showMagStripe && (
                <div className="pointer-events-none absolute inset-x-0 top-3 h-8 bg-black/50" />
            )}

            {/* ── Body ─────────────────────────────────────────────── */}
            <div
                className="flex flex-1 flex-col items-center justify-between px-4 pb-4"
                style={{ paddingTop: showMagStripe ? '4rem' : '1rem' }}
            >
                {/* Official header */}
                <p className="text-[9px] font-bold uppercase tracking-wider" style={{ color: textColor }}>
                    {t('idCards.officialCard')}
                </p>

                {/* QR code block */}
                <div className="flex flex-col items-center gap-1.5">
                    <span className="text-[7px] font-semibold uppercase tracking-widest text-center" style={{ color: textColor }}>
                        {scanLabel}
                    </span>

                    {qrValue ? (
                        <div className="rounded-xl bg-white shadow-lg" style={{ padding: 6 }}>
                            <QRCodeSVG
                                value={qrValue}
                                size={110}
                                level="M"
                                bgColor="#FFFFFF"
                                fgColor="#0F172A"
                            />
                        </div>
                    ) : (
                        <div
                            className="flex items-center justify-center rounded-xl border-2 border-dashed border-white/20 bg-white/5"
                            style={{ width: 122, height: 122 }}
                        >
                            <span className="px-2 text-center text-[7px] leading-tight text-white/40">
                                {t('idCards.qrOnPrint')}
                            </span>
                        </div>
                    )}

                    <span className="max-w-[100px] text-center text-[6px] leading-tight" style={{ color: textColor, opacity: 0.5 }}>
                        {t('idCards.qrNoPersonalInfo')}
                    </span>
                </div>

                {/* Property notice */}
                <p className="text-center text-[7px] leading-relaxed" style={{ color: textColor, opacity: 0.65 }}>
                    {t('idCards.propertyNotice')}
                </p>

                {verificationUrl && (
                    <p className="break-all text-center text-[6px] font-mono leading-tight" style={{ color: textColor, opacity: 0.45 }}>
                        {verificationUrl}
                    </p>
                )}

                {/* Card number + contact */}
                <div className="flex w-full flex-col items-center gap-0.5">
                    <div className="mb-1 h-px w-full" style={{ background: 'rgba(255,255,255,0.08)' }} />
                    <p className="text-[8px] font-mono font-semibold tracking-wider" style={{ color: textColor }}>
                        {cardNumber}
                    </p>
                    {supportContact && (
                        <p className="text-center text-[6px] leading-tight" style={{ color: textColor, opacity: 0.55 }}>
                            {supportContact}
                        </p>
                    )}
                    <p className="text-center text-[6px] leading-tight" style={{ color: textColor, opacity: 0.4 }}>
                        {returnAddress}
                    </p>
                </div>
            </div>

            {/* ── Bottom thin accent ────────────────────────────────── */}
            <div
                className="pointer-events-none absolute inset-x-0 bottom-0 h-1"
                style={{ background: `linear-gradient(to right, rgba(255,255,255,0.08), rgba(255,255,255,0.03))` }}
            />
        </div>
    );
}
