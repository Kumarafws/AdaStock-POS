<?php

namespace App\Models;

use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => LocationType::class,
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'assigned_store_id');
    }

    public function scopeStores($query)
    {
        return $query->where('type', LocationType::STORE);
    }

    public function scopeWarehouses($query)
    {
        return $query->where('type', LocationType::WAREHOUSE);
    }

    public function scopeQuarantine($query)
    {
        return $query->where('type', LocationType::QUARANTINE);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
