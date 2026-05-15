<?php

declare(strict_types=1);

namespace App\Actions\CodeRules;

use App\Actions\Audit\WriteAuditLogAction;
use App\Enums\AuditEventType;
use App\Models\CodeRule;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateCodeRuleAction
{
    public function __construct(
        private readonly WriteAuditLogAction $writeAuditLogAction,
    ) {}

    public function execute(CodeRule $codeRule, array $attributes, ?User $actor = null): CodeRule
    {
        $oldValues = $codeRule->toArray();

        $this->ensureNoActiveConflict($codeRule, $attributes);

        $codeRule->fill([
            ...$attributes,
            'active_scope_key' => ($attributes['is_active'] ?? $codeRule->is_active)
                ? CodeRule::buildActiveScopeKey(
                    $attributes['entity_type'] ?? $codeRule->entity_type,
                    $attributes['scope_type'] ?? $codeRule->scope_type,
                    $attributes['scope_id'] ?? $codeRule->scope_id,
                )
                : null,
            'updated_by' => $actor?->getKey(),
        ])->save();

        $this->writeAuditLogAction->execute(
            AuditEventType::CodeRuleUpdated,
            $actor,
            $codeRule->fresh(),
            null,
            $oldValues,
            $codeRule->fresh()->toArray(),
        );

        return $codeRule->fresh();
    }

    private function ensureNoActiveConflict(CodeRule $codeRule, array $attributes): void
    {
        if (! ($attributes['is_active'] ?? $codeRule->is_active)) {
            return;
        }

        $exists = CodeRule::query()
            ->whereKeyNot($codeRule->getKey())
            ->where('entity_type', $attributes['entity_type'] ?? $codeRule->entity_type)
            ->where('scope_type', $attributes['scope_type'] ?? $codeRule->scope_type)
            ->where('scope_id', $attributes['scope_id'] ?? $codeRule->scope_id)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'entity_type' => __('code-rules.duplicate_active_rule'),
            ]);
        }
    }
}
