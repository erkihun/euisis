<?php

declare(strict_types=1);

return [
    'session_expired'        => 'የክፍለ ጊዜዎ አብቅቷል። እባክዎ እንደገና ይግቡ።',
    'access_denied'          => 'ፍቃድ የለም።',
    'scope_denied'           => 'የድርጅት ወሰንዎ ይህን መዝገብ አይጨምርም።',
    'too_many_attempts'      => 'ብዙ የመግቢያ ሙከራዎች። እባክዎ :seconds ሰከንዶች ይጠብቁ።',
    'strong_password_required' => 'የሚስጥር ቃል ዝቅተኛ የደህንነት መስፈርቶችን ማሟላት አለበት።',

    // ምዝገባ ቁጥጥር
    'registration_disabled'    => 'ምዝገባ ተሰናክሏል። የተጠቃሚ መለያዎች በአስተዳዳሪዎች ብቻ ይፈጠራሉ።',

    // የብዙ-ደረጃ ማረጋገጫ (TOTP)
    'mfa_required'             => 'ለመለያዎ የብዙ-ደረጃ ማረጋገጫ ያስፈልጋል።',
    'mfa_challenge_failed'     => 'የተሳሳተ የማረጋገጫ ኮድ።',
    'mfa_enabled'              => 'የብዙ-ደረጃ ማረጋገጫ ነቅቷል።',
    'mfa_disabled'             => 'የብዙ-ደረጃ ማረጋገጫ ተሰናክሏል።',
    'mfa_already_enabled'      => 'የብዙ-ደረጃ ማረጋገጫ አስቀድሞ ነቅቷል።',
    'mfa_disable_not_allowed'  => 'ለሚናዎ የብዙ-ደረጃ ማረጋገጫን ማሰናከል አይቻልም።',
    'mfa_setup_required'       => 'ለመቀጠል እባክዎ የብዙ-ደረጃ ማረጋገጫ ቅንብርን ያጠናቅቁ።',
    'mfa_challenge_required'   => 'ለመቀጠል እባክዎ የማረጋገጫ ኮድዎን ያረጋግጡ።',
    'mfa_recovery_codes_note'  => 'እነዚህን የመልሶ ማግኛ ኮዶች በደህንነቱ የተጠበቀ ቦታ ያስቀምጡ። እያንዳንዱ ኮድ አንድ ጊዜ ብቻ ይጠቀምበት ይችላል።',
];
