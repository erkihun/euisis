<?php

declare(strict_types=1);

return [
    'session_expired'           => 'የክፍለ ጊዜዎ አብቅቷል። እባክዎ እንደገና ይግቡ።',
    'access_denied'             => 'ፍቃድ የለም።',
    'scope_denied'              => 'የተቋም ወሰንዎ ይህን መዝገብ አይጨምርም።',
    'too_many_attempts'         => 'ብዙ የመግቢያ ሙከራዎች። እባክዎ :seconds ሰከንዶች ይጠብቁ።',
    'strong_password_required'  => 'የሚስጥር ቃል ዝቅተኛ የደህንነት መስፈርቶችን ማሟላት አለበት።',

    // ምዝገባ ቁጥጥር
    'registration_disabled'     => 'ምዝገባ ተሰናክሏል። የተጠቃሚ መለያዎች በአስተዳዳሪዎች ብቻ ይፈጠራሉ።',

    // የብዙ-ደረጃ ማረጋገጫ (TOTP)
    'mfa_required'              => 'ለመለያዎ የብዙ-ደረጃ ማረጋገጫ ያስፈልጋል።',
    'mfa_challenge_failed'      => 'የተሳሳተ የማረጋገጫ ኮድ።',
    'mfa_enabled'               => 'የብዙ-ደረጃ ማረጋገጫ ነቅቷል።',
    'mfa_disabled'              => 'የብዙ-ደረጃ ማረጋገጫ ተሰናክሏል።',
    'mfa_already_enabled'       => 'የብዙ-ደረጃ ማረጋገጫ አስቀድሞ ነቅቷል።',
    'mfa_disable_not_allowed'   => 'ለሚናዎ የብዙ-ደረጃ ማረጋገጫን ማሰናከል አይቻልም።',
    'mfa_setup_required'        => 'ለመቀጠል እባክዎ የብዙ-ደረጃ ማረጋገጫ ቅንብርን ያጠናቅቁ።',
    'mfa_challenge_required'    => 'ለመቀጠል እባክዎ የማረጋገጫ ኮድዎን ያረጋግጡ።',
    'mfa_recovery_codes_note'   => 'እነዚህን የመልሶ ማግኛ ኮዶች በደህንነቱ የተጠበቀ ቦታ ያስቀምጡ። እያንዳንዱ ኮድ አንድ ጊዜ ብቻ ይጠቀምበት ይችላል።',

    // INSA-required የደህንነት መልዕክቶች
    'upload_rejected'           => 'ፋይል መጫን ተከልክሏል።',
    'unsafe_file_type'          => 'የፋይሉ አይነት አይፈቀድም። የተፈቀዱ አይነቶች ብቻ መጫን ይቻላል።',
    'rate_limit_exceeded'       => 'የጥያቄ ገደቡ ተሻገረ። እባክዎ ጥቂት ጠብቀው እንደገና ይሞክሩ።',
    'sensitive_info_protected'  => 'ሚስጥራዊ መረጃ ተጠብቋል እና ሊወጣ አይችልም።',
    'org_scope_denied'          => 'የተቋም ወሰንዎ ይህን መዝገብ አይጨምርም።',
    'action_not_permitted'      => 'ይህ ድርጊት ለመለያዎ አይፈቀድም።',
    'security_headers_enabled'  => 'የደህንነት ራስጌዎች ነቅተዋል።',
    'secure_config_required'    => 'ለዚህ አካባቢ ደህንነቱ የተጠበቀ ቅንብር ያስፈልጋል።',
    'invalid_request'           => 'ትክክል ያልሆነ ጥያቄ። እባክዎ ግቤቱን ያረጋግጡና እንደገና ይሞክሩ።',
    'unauthorized'              => 'ይህን ሀብት ለማግኘት ወደ ስርዓቱ መግባት አለቦት።',
    'forbidden'                 => 'ይህን ድርጊት ለመፈጸም ፈቃድ የለዎትም።',
    'security_policy'           => 'የደህንነት ፖሊሲ',
    'pii_view_denied'           => 'ሚስጥራዊ የግል መረጃ ለማየት ፈቃድ የለዎትም።',
    'security_audit'            => 'የደህንነት ምርመራ',
];
