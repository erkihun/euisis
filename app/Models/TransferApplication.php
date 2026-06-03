<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransferApplicationStatus;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferApplication extends Model
{
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'announcement_id',
        'employee_id',
        'current_assignment_id',
        'releasing_organization_id',
        'receiving_organization_id',
        'status',
        'eligibility_snapshot',
        'applicant_notes',
        'selected_at',
        'selected_by',
        'rejected_reason',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferApplicationStatus::class,
            'eligibility_snapshot' => 'array',
            'selected_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(TransferAnnouncement::class, 'announcement_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function currentAssignment(): BelongsTo
    {
        return $this->belongsTo(EmployeeAssignment::class, 'current_assignment_id');
    }

    public function releasingOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'releasing_organization_id');
    }

    public function receivingOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'receiving_organization_id');
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TransferApplicationDocument::class);
    }

    public function screeningReviews(): HasMany
    {
        return $this->hasMany(TransferScreeningReview::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TransferApproval::class);
    }

    public function ruleOverrides(): HasMany
    {
        return $this->hasMany(TransferRuleOverride::class);
    }

    public function releaseApproval(): HasOne
    {
        return $this->hasOne(TransferApproval::class)->where('approval_type', 'release');
    }

    public function receivingApproval(): HasOne
    {
        return $this->hasOne(TransferApproval::class)->where('approval_type', 'receiving');
    }

    public function finalApproval(): HasOne
    {
        return $this->hasOne(TransferApproval::class)->where('approval_type', 'final');
    }

    public function scopePending(Builder $query): void
    {
        $query->whereIn('status', array_map(
            fn (TransferApplicationStatus $s) => $s->value,
            array_filter(
                TransferApplicationStatus::cases(),
                fn (TransferApplicationStatus $s) => $s->isPending(),
            ),
        ));
    }

    public function allRequiredApprovalsGranted(TransferSetting $settings): bool
    {
        if ($settings->releasing_consent_required) {
            $release = $this->approvals->firstWhere('approval_type', 'release');
            if ($release?->status !== 'approved') {
                return false;
            }
        }

        if ($settings->receiving_consent_required) {
            $receiving = $this->approvals->firstWhere('approval_type', 'receiving');
            if ($receiving?->status !== 'approved') {
                return false;
            }
        }

        if ($settings->final_approval_required) {
            $final = $this->approvals->firstWhere('approval_type', 'final');
            if ($final?->status !== 'approved') {
                return false;
            }
        }

        return true;
    }
}
