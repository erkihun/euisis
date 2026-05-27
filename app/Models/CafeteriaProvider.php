<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CafeteriaProvider extends Model
{
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'service_provider_id',
        'code',
        'name_en',
        'name_am',
        'organization_id',
        'assigned_scope_type',
        'contact_person',
        'phone_number',
        'email',
        'location',
        'is_active',
        'metadata',
        'created_by',
        'updated_by',
        'deleted_by',
        'deletion_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CafeteriaTransaction::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cafeteria_provider_users')
            ->withPivot(['role', 'is_active', 'assigned_by', 'effective_from', 'effective_to'])
            ->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
