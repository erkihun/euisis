<?php

declare(strict_types=1);

namespace App\Actions\Positions;

use App\Actions\Audit\WriteAuditLogAction;
use App\Actions\CodeRules\GenerateCodeAction;
use App\Enums\AuditEventType;
use App\Enums\CodeRuleEntityType;
use App\Models\Position;
use App\Models\User;

readonly class CreatePositionAction
{
    public function __construct(
        private WriteAuditLogAction $writeAuditLogAction,
        private GenerateCodeAction $generateCodeAction,
    ) {}

    public function execute(array $attributes, User $actor): Position
    {
        $attributes['job_position_code'] = $this->generateCodeAction->execute(
            CodeRuleEntityType::Position,
            [
                'organization_id' => $attributes['organization_id'] ?? null,
                'organization_unit_id' => $attributes['organization_unit_id'] ?? null,
            ],
            $actor,
            $attributes['job_position_code'] ?? null,
            'job_position_code',
        );

        $position = Position::query()->create($attributes);

        $this->writeAuditLogAction->execute(
            AuditEventType::PositionCreated,
            $actor,
            $position,
            $position->organization_id,
            newValues: $position->toArray(),
        );

        return $position;
    }
}
