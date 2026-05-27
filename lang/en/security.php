<?php

declare(strict_types=1);

return [
    'session_expired'        => 'Your session has expired. Please log in again.',
    'access_denied'          => 'Access denied.',
    'scope_denied'           => 'Your organization scope does not include this record.',
    'too_many_attempts'      => 'Too many login attempts. Please wait :seconds seconds.',
    'strong_password_required' => 'Password must meet minimum security requirements.',

    // Registration gating
    'registration_disabled'    => 'Registration is disabled. User accounts are created by administrators.',

    // Multi-Factor Authentication (TOTP)
    'mfa_required'             => 'Multi-factor authentication is required for your account.',
    'mfa_challenge_failed'     => 'Invalid authentication code.',
    'mfa_enabled'              => 'Multi-factor authentication enabled.',
    'mfa_disabled'             => 'Multi-factor authentication disabled.',
    'mfa_already_enabled'      => 'Multi-factor authentication is already enabled.',
    'mfa_disable_not_allowed'  => 'Multi-factor authentication cannot be disabled for your role.',
    'mfa_setup_required'       => 'Please complete multi-factor authentication setup to continue.',
    'mfa_challenge_required'   => 'Please verify your authentication code to continue.',
    'mfa_recovery_codes_note'  => 'Save these recovery codes in a safe place. Each code can only be used once.',
];
