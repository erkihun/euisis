const auth = {
    login: 'Log in',
    logout: 'Log out',
    register: 'Register',
    email: 'Email',
    password: 'Password',
    confirmPassword: 'Confirm Password',
    rememberMe: 'Remember me',
    forgotPassword: 'Forgot your password?',
    resetPassword: 'Reset Password',
    sendResetLink: 'Email Password Reset Link',
    verifyEmail: 'Verify Email Address',
    resendVerification: 'Resend Verification Email',
    confirmPasswordMessage: 'Please confirm your password before continuing.',

    // Registration gating
    registrationDisabled: 'Registration is disabled.',
    registrationAdminOnly: 'User accounts are created by authorized administrators.',

    // Multi-Factor Authentication (TOTP)
    mfa: 'Multi-Factor Authentication',
    mfaSetup: 'Set Up Multi-Factor Authentication',
    mfaSetupDescription:
        'Scan the QR code with your authenticator app, then enter the 6-digit code to confirm.',
    mfaChallenge: 'Verify Authentication Code',
    mfaChallengeDescription: 'Enter the 6-digit code from your authenticator app.',
    mfaCode: 'Authentication Code',
    mfaRequired: 'Multi-factor authentication is required for your role.',
    mfaSetupSuccess: 'MFA setup completed successfully.',
    mfaInvalidCode: 'Invalid authentication code. Please try again.',
    mfaVerified: 'Authentication code verified.',
    mfaManualKey: 'Manual setup key',
    mfaRecoveryCodes: 'Recovery Codes',
    mfaRecoveryCodesNote:
        'Save these recovery codes in a safe place. Each code can only be used once.',
    mfaDisabled: 'Multi-factor authentication has been disabled.',
    mfaDisabledNotAllowed: 'MFA cannot be disabled for your role.',
    mfaUseRecoveryCode: 'Use a recovery code instead',
    mfaSubmit: 'Verify',
    mfaContinue: 'Continue',
    mfaConfirmSetup: 'Confirm setup',
    mfaDisable: 'Disable MFA',
} as const;

export default auth;
