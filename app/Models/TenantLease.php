<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantLease extends Model
{
    use HasFactory;

    protected $table = 'tenant_leases';

    protected $fillable = [
        'unit_id',
        'tenant_name',
        'tenant_email',
        'tenant_phone',
        'lease_start',
        'lease_end',
        'monthly_rent',
        'deposit',
        'status',
        'notes',
    ];

    protected $casts = [
        'lease_start'  => 'date',
        'lease_end'    => 'date',
        'monthly_rent' => 'decimal:2',
        'deposit'      => 'decimal:2',
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_UPCOMING  = 'upcoming';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_TERMINATED = 'terminated';

    /**
     * The unit this lease is bound to.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * All rent invoices generated against this lease.
     */
    public function rentInvoices(): HasMany
    {
        return $this->hasMany(RentInvoice::class);
    }
}
