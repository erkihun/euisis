<?php

declare(strict_types=1);

namespace App\Actions\InstitutionOffices;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\InstitutionOffice;
use App\Models\User;
use Illuminate\Http\Request;

readonly class CreateInstitutionOfficeAction
{
    public function __construct(
        private WriteAuditLogAction $writeAuditLogAction,
    ) {}

    public function execute(array $attributes, User $actor, ?Request $request = null): InstitutionOffice
    {
        $attributes['created_by'] = $actor->getKey();
        $attributes['updated_by'] = $actor->getKey();

        $office = InstitutionOffice::query()->create($attributes);

        $this->writeAuditLogAction->execute(
            AuditEventType::InstitutionOfficeCreated,
            $actor,
            $office,
            $office->institution_id,
            newValues: $office->toArray(),
            request: $request,
        );

        return $office;
    }
}
