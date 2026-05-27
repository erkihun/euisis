<?php

declare(strict_types=1);

return [
    'accepted' => 'የ:attribute መስክ መቀበል አለበት።',
    'after' => 'የ:attribute መስክ ከ:date በኋላ የሆነ ቀን መሆን አለበት።',
    'after_or_equal' => 'የ:attribute መስክ ከ:date በኋላ ወይም እኩል የሆነ ቀን መሆን አለበት።',
    'date' => 'የ:attribute መስክ ትክክለኛ ቀን አይደለም።',
    'different' => 'የ:attribute እና :other መስኮች የተለያዩ መሆን አለባቸው።',
    'email' => 'የ:attribute መስክ ትክክለኛ የኢሜይል አድራሻ መሆን አለበት።',
    'exists' => 'የተመረጠው :attribute ትክክል አይደለም።',
    'image' => 'የ:attribute መስክ ምስል መሆን አለበት።',
    'integer' => 'የ:attribute መስክ ሙሉ ቁጥር መሆን አለበት።',
    'min' => [
        'file' => 'የ:attribute መጠን ቢያንስ :min ኪሎባይት መሆን አለበት።',
        'numeric' => 'የ:attribute መስክ ቢያንስ :min መሆን አለበት።',
        'string' => 'የ:attribute መስክ ቢያንስ :min ፊደላት መሆን አለበት።',
    ],
    'max' => [
        'file' => 'የ:attribute መጠን ከ:max ኪሎባይት መብለጥ የለበትም።',
        'numeric' => 'የ:attribute መስክ ከ:max መብለጥ የለበትም።',
        'string' => 'የ:attribute መስክ ከ:max ፊደላት መብለጥ የለበትም።',
    ],
    'mimes' => 'የ:attribute መስክ ከእነዚህ አይነቶች አንዱ መሆን አለበት: :values።',
    'numeric' => 'የ:attribute መስክ ቁጥር መሆን አለበት።',
    'regex' => 'የ:attribute ቅርጸት ትክክል አይደለም።',
    'required' => 'የ:attribute መስክ አስፈላጊ ነው።',
    'required_with' => ':values ሲኖር የ:attribute መስክ አስፈላጊ ነው።',
    'string' => 'የ:attribute መስክ ጽሑፍ መሆን አለበት።',
    'unique' => 'የ:attribute እሴት ከዚህ በፊት ተጠቅመዋል።',
    'url' => 'የ:attribute መስክ ትክክለኛ URL መሆን አለበት።',
    'uuid' => 'የ:attribute መስክ ትክክለኛ UUID መሆን አለበት።',

    'attributes' => [
        'organization_type_id' => 'የድርጅት አይነት',
        'parent_organization_id' => 'ወላጅ ድርጅት',
        'child_organization_id' => 'ልጅ ድርጅት',
        'hierarchy_version_id' => 'የተዋረድ ስሪት',
        'relationship_type' => 'የግንኙነት አይነት',
        'effective_from' => 'የመጀመሪያ ቀን',
        'effective_to' => 'የማብቂያ ቀን',
        'branding_primary_color' => 'ዋና ቀለም',
        'branding_secondary_color' => 'ሁለተኛ ቀለም',
    ],
    'position_already_occupied' => 'ይህ ስራ መደብ ቀድሞ ለሌላ ሰራተኛ ተመድቧል።',
];
