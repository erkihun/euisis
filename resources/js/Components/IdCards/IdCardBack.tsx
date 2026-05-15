import type { CSSProperties } from 'react';
import { QRCodeSVG } from 'qrcode.react';
import { useLocale } from '@/hooks/useLocale';
import { useSystemSettings } from '@/hooks/useSystemSettings';

type IdCardBackProps = {
    cardNumber: string;
    /** Verification URL or payload to encode in the QR. No PII — UUID ref only. */
    qrValue?: string | null;
    /** Extra styles merged onto the root div — use to force explicit height for html-to-image export */
    rootStyle?: CSSProperties;
};

export default function IdCardBack({ cardNumber, qrValue, rootStyle }: IdCardBackProps) {
    const { t, locale } = useLocale();
    const { getString, getBoolean } = useSystemSettings();

    const backFrom      = getString('id_cards.back_bg_from', '#1E293B');
    const backTo        = getString('id_cards.back_bg_to', '#0F172A');
    const textColor     = getString('id_cards.back_text_color', '#94A3B8');
    const showMagStripe = getBoolean('id_cards.show_magnetic_stripe', true);
    const qrSizeRaw     = getString('id_cards.qr_size', '96');
    const qrSize        = parseInt(qrSizeRaw, 10) || 96;
    const padding       = getString('id_cards.card_padding', 'normal');

    const returnAddress = locale === 'am'
        ? getString('id_cards.return_address_am', 'አዲስ አበባ ከተማ አስተዳደር፣ የሲቪል ሰርቪስና ሰው ሃብት ልማት ቢሮ')
        : getString('id_cards.return_address_en', 'Addis Ababa City Administration, Public Service & HRD Bureau');

    const supportContact = getString('id_cards.support_contact', '');
    const verificationUrl = getString('id_cards.verification_url', '');

    const padCls = padding === 'compact' ? 'px-3' : padding === 'spacious' ? 'px-5' : 'px-4';

    const scanToVerifyLabel = locale === 'am'
        ? 'ለማረጋገጥ ስካን ያድርጉ'
        : t('idCards.scanToVerify');

    return (
        <div
            className="relative overflow-hidden rounded-xl shadow-xl"
            style={{
                aspectRatio: '85.6/54',
                width: '100%',
                maxWidth: 400,
                background: `linear-gradient(135deg, ${backFrom} 0%, ${backTo} 100%)`,
                ...rootStyle,
            }}
        >
            {/* Security dot pattern */}
            <div
                className="absolute inset-0 pointer-events-none"
                style={{
                    backgroundImage: `radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px)`,
                    backgroundSize: '10px 10px',
                }}
            />

            {/* Magnetic stripe simulation */}
            {showMagStripe && (
                <div className="absolute inset-x-0 top-3 h-6 bg-black/50 pointer-events-none" />
            )}

            {/* Main content area */}
            <div className={`absolute inset-x-0 bottom-0 flex gap-3 ${padCls} pt-0 pb-2`} style={{ top: showMagStripe ? '2.5rem' : '0.75rem' }}>

                {/* QR code block — maximised, centered vertically */}
                <div className="flex shrink-0 flex-col items-center justify-center gap-1">
                    <span className="text-[7px] font-semibold uppercase tracking-widest text-center mb-0.5" style={{ color: textColor }}>
                        {scanToVerifyLabel}
                    </span>
                    {qrValue ? (
                        <div
                            className="rounded-md bg-white shadow-md"
                            style={{ padding: '4px' }}
                        >
                            <QRCodeSVG
                                value={qrValue}
                                size={qrSize}
                                level="M"
                                bgColor="#FFFFFF"
                                fgColor="#0F172A"
                            />
                        </div>
                    ) : (
                        <div
                            className="flex items-center justify-center rounded-md border-2 border-dashed border-white/20 bg-white/5"
                            style={{ width: qrSize + 8, height: qrSize + 8 }}
                        >
                            <span className="text-center text-[7px] leading-tight text-white/40 px-1">
                                {t('idCards.qrOnPrint')}
                            </span>
                        </div>
                    )}
                    <span className="text-[6px] text-center leading-tight mt-0.5 max-w-[80px]" style={{ color: textColor, opacity: 0.55 }}>
                        {t('idCards.qrNoPersonalInfo')}
                    </span>
                </div>

                {/* Text column */}
                <div className="flex min-w-0 flex-1 flex-col justify-between self-stretch">
                    <div className="space-y-1">
                        <p className="text-[9px] font-bold uppercase tracking-wider" style={{ color: textColor }}>
                            {t('idCards.officialCard')}
                        </p>
                        <p className="text-[7px] leading-relaxed" style={{ color: textColor, opacity: 0.65 }}>
                            {t('idCards.propertyNotice')}
                        </p>
                        {verificationUrl && (
                            <p className="text-[7px] font-mono break-all leading-tight" style={{ color: textColor, opacity: 0.5 }}>
                                {verificationUrl}
                            </p>
                        )}
                    </div>

                    <div className="space-y-0.5 mt-auto">
                        <p className="font-mono text-[8px] font-semibold tracking-wider" style={{ color: textColor }}>
                            {cardNumber}
                        </p>
                        {supportContact && (
                            <p className="text-[7px] leading-tight" style={{ color: textColor, opacity: 0.55 }}>
                                {supportContact}
                            </p>
                        )}
                        <p className="text-[7px] leading-tight truncate" style={{ color: textColor, opacity: 0.45 }}>
                            {returnAddress}
                        </p>
                    </div>
                </div>
            </div>

            {/* Bottom thin accent */}
            <div
                className="absolute inset-x-0 bottom-0 h-1 pointer-events-none"
                style={{ background: `linear-gradient(to right, rgba(255,255,255,0.08), rgba(255,255,255,0.03))` }}
            />
        </div>
    );
}
