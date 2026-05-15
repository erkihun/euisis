<?php

declare(strict_types=1);

namespace App\Actions\Occupations;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\Occupation;
use App\Models\User;

readonly class UpdateOccupationAction
{
    public function __construct(private WriteAuditLogAction $writeAuditLogAction) {}

    public function execute(Occupation $occupation, array $attributes, User $actor): Occupation
    {
        $oldValues = $occupation->toArray();

        if (isset($attributes['isco_code']) && $attributes['isco_code'] !== '') {
            $attributes['isco_code'] = strtoupper(trim($attributes['isco_code']));
            $attributes['code'] = $attributes['isco_code'];
        }

        $attributes['updated_by'] = $actor->getKey();

        $occupation->update($attributes);

        $this->writeAuditLogAction->execute(
            AuditEventType::OccupationUpdated,
            $actor,
            $occupation->fresh(),
            null,
            oldValues: $oldValues,
            newValues: $occupation->fresh()->toArray(),
        );

        return $occupation->fresh();
    }
}
