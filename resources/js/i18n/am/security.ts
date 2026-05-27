const security = {
    sessionExpired: 'የክፍለ ጊዜዎ አብቅቷል። እባክዎ እንደገና ይግቡ።',
    accessDenied: 'ፍቃድ የለም። ይህን ድርጊት ለማከናወን ፈቃድ የለዎትም።',
    scopeDenied: 'የድርጅት ወሰንዎ ይህን መዝገብ አይጨምርም።',
    tooManyAttempts: 'ብዙ ሙከራዎች። እባክዎ ጠብቀው እንደገና ይሞክሩ።',
    strongPasswordRequired: 'የሚስጥር ቃል ዝቅተኛ የደህንነት መስፈርቶችን ማሟላት አለበት።',
    securitySettingsUpdated: 'የደህንነት ቅንብሮች በተሳካ ሁኔታ ተዘምነዋል።',
    auditLog: 'የኦዲት ምዝግብ ማስታወሻ',
    incidentResponse: 'የአደጋ ምላሽ',
    vulnerabilityManagement: 'የተጋላጭነት አያያዝ',

    // ምዝገባ ቁጥጥር
    registrationDisabled: 'ምዝገባ ተሰናክሏል። የተጠቃሚ መለያዎች በአስተዳዳሪዎች ብቻ ይፈጠራሉ።',

    // MFA
    mfaRequired: 'ለመለያዎ የብዙ-ደረጃ ማረጋገጫ ያስፈልጋል።',
    mfaChallengeFailed: 'የተሳሳተ የማረጋገጫ ኮድ።',
    mfaEnabled: 'የብዙ-ደረጃ ማረጋገጫ ነቅቷል።',
    mfaDisabled: 'የብዙ-ደረጃ ማረጋገጫ ተሰናክሏል።',
    mfaAlreadyEnabled: 'የብዙ-ደረጃ ማረጋገጫ አስቀድሞ ነቅቷል።',
    mfaDisableNotAllowed: 'ለሚናዎ የብዙ-ደረጃ ማረጋገጫን ማሰናከል አይቻልም።',
    mfaSetupRequired: 'ለመቀጠል እባክዎ የብዙ-ደረጃ ማረጋገጫ ቅንብርን ያጠናቅቁ።',
    mfaChallengeRequired: 'ለመቀጠል እባክዎ የማረጋገጫ ኮድዎን ያረጋግጡ።',
    mfaRecoveryCodesNote: 'እነዚህን የመልሶ ማግኛ ኮዶች በደህንነቱ በተጠበቀ ቦታ ያስቀምጡ። እያንዳንዱ ኮድ አንድ ጊዜ ብቻ ይጠቀምበት ይችላል።',
} as const;

export default security;
export type SecurityTranslationKeys = typeof security;
