<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'label',
        'floor',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'market_rent',
        'is_occupied',
    ];

    protected $casts = [
        'market_rent' => 'decimal:2',
        'is_occupied' => 'boolean',
    ];

    /**
     * Parent property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * All leases ever recorded against this unit (history).
     */
    public function leases(): HasMany
    {
        return $this->hasMany(TenantLease::class);
    }

    /**
     * The currently active lease (if any).
     */
    public function activeLease()
    {
        return $this->hasOne(TenantLease::class)
            ->where('status', 'active')
            ->latest('lease_start');
    }

    /**
     * Maintenance requests scoped to this unit.
     */
    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
