<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationType extends Model
{
    use HasUuidPrimaryKey;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'prefix',
        'name_en',
        'name_am',
        'description',
        'description_en',
        'description_am',
        'is_active',
        'sort_order',
        'is_demo',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_demo' => 'bool',
            'is_active' => 'bool',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    protected function prefix(): Attribute
    {
        return Attribute::make(
            set: static fn (?string $value): ?string => $value === null || trim($value) === ''
                ? null
                : mb_strtoupper(trim($value), 'UTF-8'),
        );
    }
}
