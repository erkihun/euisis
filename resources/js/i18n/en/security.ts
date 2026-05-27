const security = {
    sessionExpired: 'Your session has expired. Please log in again.',
    accessDenied: 'Access denied. You do not have permission to perform this action.',
    scopeDenied: 'Your organization scope does not include this record.',
    tooManyAttempts: 'Too many attempts. Please wait before trying again.',
    strongPasswordRequired: 'Password must meet the minimum security requirements.',
    securitySettingsUpdated: 'Security settings updated successfully.',
    auditLog: 'Audit Log',
    incidentResponse: 'Incident Response',
    vulnerabilityManagement: 'Vulnerability Management',

    // Registration gating
    registrationDisabled: 'Registration is disabled. User accounts are created by administrators.',

    // MFA
    mfaRequired: 'Multi-factor authentication is required for your account.',
    mfaChallengeFailed: 'Invalid authentication code.',
    mfaEnabled: 'Multi-factor authentication enabled.',
    mfaDisabled: 'Multi-factor authentication disabled.',
    mfaAlreadyEnabled: 'Multi-factor authentication is already enabled.',
    mfaDisableNotAllowed: 'Multi-factor authentication cannot be disabled for your role.',
    mfaSetupRequired: 'Please complete multi-factor authentication setup to continue.',
    mfaChallengeRequired: 'Please verify your authentication code to continue.',
    mfaRecoveryCodesNote: 'Save these recovery codes in a safe place. Each code can only be used once.',
} as const;

export default security;
export type SecurityTranslationKeys = typeof security;
