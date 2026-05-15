<?php

declare(strict_types=1);

namespace App\Actions\CodeRules;

use App\Models\CodeRule;
use App\Services\CodeGeneration\CodeGeneratorService;

class PreviewCodeRuleAction
{
    public function __construct(
        private readonly CodeGeneratorService $codeGeneratorService,
    ) {}

    public function execute(array $attributes, array $context = []): string
    {
        $rule = new CodeRule($attributes);
        $rule->next_number = (int) ($attributes['next_number'] ?? 1);
        $rule->sequence_length = (int) ($attributes['sequence_length'] ?? 4);

        return $this->codeGeneratorService->preview($rule, $context);
    }

    public function executeForRule(CodeRule $codeRule, array $context = []): string
    {
        return $this->codeGeneratorService->preview($codeRule, $context);
    }
}
