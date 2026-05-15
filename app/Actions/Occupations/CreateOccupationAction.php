<?php

declare(strict_types=1);

namespace App\Actions\Occupations;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\Occupation;
use App\Models\User;

readonly class CreateOccupationAction
{
    public function __construct(
        private WriteAuditLogAction $writeAuditLogAction,
    ) {}

    public function execute(array $attributes, User $actor): Occupation
    {
        if (isset($attributes['isco_code']) && $attributes['isco_code'] !== '') {
            $attributes['isco_code'] = strtoupper(trim($attributes['isco_code']));
        }

        // Mirror isco_code into legacy code column to satisfy NOT NULL/unique constraint.
        if (! isset($attributes['code']) || $attributes['code'] === '' || $attributes['code'] === null) {
            $attributes['code'] = $attributes['isco_code'] ?? null;
        }

        $attributes['created_by'] = $actor->getKey();
        $attributes['updated_by'] = $actor->getKey();

        $occupation = Occupation::query()->create($attributes);

        $this->writeAuditLogAction->execute(
            AuditEventType::OccupationCreated,
            $actor,
            $occupation,
            null,
            newValues: $occupation->toArray(),
        );

        return $occupation;
    }
}
