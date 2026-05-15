<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationRelationshipType: string
{
    case ReportsTo = 'reports_to';
    case GeographicallyUnder = 'geographically_under';
    case ServiceScope = 'service_scope';
    case Oversight = 'oversight';
}
