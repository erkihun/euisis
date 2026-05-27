const auth = {
    login: 'ግባ',
    logout: 'ውጣ',
    register: 'ምዝገባ',
    email: 'ኢሜይል',
    password: 'የይለፍ ቃል',
    confirmPassword: 'የይለፍ ቃል አረጋግጥ',
    rememberMe: 'አስታወስ',
    forgotPassword: 'የይለፍ ቃልዎን ረሱ?',
    resetPassword: 'የይለፍ ቃል ዳግም አስጀምር',
    sendResetLink: 'የዳግም ማስጀመሪያ ሊንክ ላክ',
    verifyEmail: 'ኢሜይል አረጋግጥ',
    resendVerification: 'ማረጋገጫ ኢሜይል ዳግም ላክ',
    confirmPasswordMessage: 'ከመቀጠልዎ በፊት የይለፍ ቃልዎን ያረጋግጡ።',

    // ምዝገባ ቁጥጥር
    registrationDisabled: 'ምዝገባ ተሰናክሏል።',
    registrationAdminOnly: 'የተጠቃሚ መለያዎች በተፈቀደላቸው አስተዳዳሪዎች ብቻ ይፈጠራሉ።',

    // የብዙ-ደረጃ ማረጋገጫ (TOTP)
    mfa: 'የብዙ-ደረጃ ማረጋገጫ',
    mfaSetup: 'የብዙ-ደረጃ ማረጋገጫ አዘጋጅ',
    mfaSetupDescription:
        'የQR ኮዱን በማረጋገጫ መተግበሪያዎ ይቃኙ፣ ከዚያም ለማረጋገጥ የ6-ቁጥር ኮዱን ያስገቡ።',
    mfaChallenge: 'የማረጋገጫ ኮድ አረጋግጥ',
    mfaChallengeDescription: 'ከማረጋገጫ መተግበሪያዎ የ6-ቁጥር ኮድ ያስገቡ።',
    mfaCode: 'የማረጋገጫ ኮድ',
    mfaRequired: 'ለሚናዎ የብዙ-ደረጃ ማረጋገጫ ያስፈልጋል።',
    mfaSetupSuccess: 'የብዙ-ደረጃ ማረጋገጫ ቅንብር በተሳካ ሁኔታ ተጠናቅቋል።',
    mfaInvalidCode: 'የተሳሳተ ማረጋገጫ ኮድ። እባክዎ እንደገና ይሞክሩ።',
    mfaVerified: 'የማረጋገጫ ኮድ ተረጋግጧል።',
    mfaManualKey: 'የእጅ ማዘጋጃ ቁልፍ',
    mfaRecoveryCodes: 'የመልሶ ማግኛ ኮዶች',
    mfaRecoveryCodesNote:
        'እነዚህን የመልሶ ማግኛ ኮዶች በደህንነቱ የተጠበቀ ቦታ ያስቀምጡ። እያንዳንዱ ኮድ አንድ ጊዜ ብቻ ሊጠቀምበት ይችላል።',
    mfaDisabled: 'የብዙ-ደረጃ ማረጋገጫ ተሰናክሏል።',
    mfaDisabledNotAllowed: 'ለሚናዎ የብዙ-ደረጃ ማረጋገጫን ማሰናከል አይቻልም።',
    mfaUseRecoveryCode: 'በምትኩ የመልሶ ማግኛ ኮድ ይጠቀሙ',
    mfaSubmit: 'አረጋግጥ',
    mfaContinue: 'ቀጥል',
    mfaConfirmSetup: 'ቅንብር አረጋግጥ',
    mfaDisable: 'MFA አሰናክል',
} as const;

export default auth;
