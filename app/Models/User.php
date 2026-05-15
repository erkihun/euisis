<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\OrganizationScope\OrganizationScopeService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_reference',
        'default_organization_id',
        'status',
        'last_login_at',
        'is_demo',
        'profile_photo_path',
        'national_id',
        'phone_number',
        'gender',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_demo' => 'bool',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function profilePhotoUrl(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return '/storage/'.$this->profile_photo_path;
    }

    public function initials(): string
    {
        $words = array_filter(explode(' ', $this->name ?? ''));
        $letters = array_map(fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)), $words);

        return implode('', array_slice($letters, 0, 2));
    }

    public function organizationScopes(): HasMany
    {
        return $this->hasMany(UserOrganizationScope::class);
    }

    /**
     * Returns the set of organization IDs accessible to this user based on active scopes.
     * An empty array means "all organizations" (Super Admin or City Admin).
     *
     * @return string[]
     */
    public function accessibleOrganizationIds(): array
    {
        return app(OrganizationScopeService::class)->accessibleOrganizationIds($this)->all();
    }
}
