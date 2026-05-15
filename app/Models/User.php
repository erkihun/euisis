<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationScopeType;
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
     * An empty array means "all organizations" (Super Admin or citywide scope).
     *
     * @return string[]
     */
    public function accessibleOrganizationIds(): array
    {
        if ($this->isSuperAdmin()) {
            return [];
        }

        $activeScopes = $this->organizationScopes()->active()->with('organization')->get();

        $hasCitywide = $activeScopes->contains(
            fn (UserOrganizationScope $s) => $s->scope_type === OrganizationScopeType::Citywide,
        );

        if ($hasCitywide) {
            return [];
        }

        $ids = [];

        foreach ($activeScopes as $scope) {
            if ($scope->organization_id === null) {
                continue;
            }

            if ($scope->scope_type === OrganizationScopeType::Subtree) {
                $ids[] = $scope->organization_id;
                $descendantIds = \DB::table('organization_closure_paths')
                    ->where('ancestor_organization_id', $scope->organization_id)
                    ->where('depth', '>', 0)
                    ->pluck('descendant_organization_id')
                    ->toArray();
                $ids = array_merge($ids, $descendantIds);
            } else {
                $ids[] = $scope->organization_id;
            }
        }

        return array_values(array_unique($ids));
    }
}
