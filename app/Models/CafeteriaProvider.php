<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CafeteriaProvider extends Model
{
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'service_provider_id',
        'provider_id',
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

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CafeteriaTransaction::class);
    }

    /** Admin user assignments (pivot in cafeteria_provider_assignments). */
    public function adminAssignments(): HasMany
    {
        return $this->hasMany(CafeteriaProviderAssignment::class);
    }

    /** Alias kept for dashboard withCount compatibility. */
    public function portalAssignments(): HasMany
    {
        return $this->adminAssignments();
    }

    /** Dedicated portal credential accounts for this provider. */
    public function providerUsers(): HasMany
    {
        return $this->hasMany(CafeteriaProviderUser::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(CafeteriaProviderBranch::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(CafeteriaMenu::class);
    }

    public function foodOrders(): HasMany
    {
        return $this->hasMany(CafeteriaFoodOrder::class);
    }

    public function providerLedgerEntries(): HasMany
    {
        return $this->hasMany(CafeteriaProviderLedgerEntry::class);
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
