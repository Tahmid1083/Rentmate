<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class RentInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_lease_id',
        'unit_id',
        'invoice_number',
        'billing_period_start',
        'billing_period_end',
        'due_date',
        'amount_due',
        'status',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end'   => 'date',
        'due_date'             => 'date',
        'amount_due'           => 'decimal:2',
    ];

    public const STATUS_OPEN      = 'open';
    public const STATUS_PARTIAL   = 'partial';
    public const STATUS_PAID      = 'paid';
    public const STATUS_OVERDUE   = 'overdue';

    /**
     * The lease this invoice belongs to.
     */
    public function lease(): BelongsTo
    {
        return $this->belongsTo(TenantLease::class, 'tenant_lease_id');
    }

    /**
     * Convenience relation back to the unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Payments recorded against this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Sum of all payment amounts (positive, non-refunded) for this invoice.
     */
    public function getPaidAmountAttribute(): string
    {
        return (string) $this->payments()->sum('amount');
    }

    /**
     * Outstanding balance = amount due − paid.
     */
    public function getBalanceAttribute(): string
    {
        return bcsub($this->amount_due, $this->paid_amount, 2);
    }

    /**
     * Recompute status based on payments + due date.
     */
    public function recomputeStatus(): void
    {
        $paid   = (float) $this->paid_amount;
        $due    = (float) $this->amount_due;

        if ($paid >= $due && $due > 0) {
            $this->status = self::STATUS_PAID;
        } elseif ($paid > 0) {
            $this->status = self::STATUS_PARTIAL;
        } elseif (now()->gt($this->due_date)) {
            $this->status = self::STATUS_OVERDUE;
        } else {
            $this->status = self::STATUS_OPEN;
        }

        $this->save();
    }

    /**
     * Compute a one-off late fee for this invoice based on the landlord's
     * settings (flat fee + percent of outstanding balance, applied only
     * after the grace days have passed).
     *
     * Returns the late fee amount as a string. Does NOT mutate the invoice.
     */
    public function computeLateFee(\App\Models\Setting $setting): string
    {
        if ($this->status === self::STATUS_PAID) {
            return '0.00';
        }

        $grace = (int) $setting->late_fee_grace_days;
        $cutoff = $this->due_date->copy()->addDays($grace);
        if (now()->lte($cutoff)) {
            return '0.00';
        }

        $flat = (float) $setting->late_fee_flat;
        $pct  = (float) $setting->late_fee_percent;
        $outstanding = (float) $this->balance;

        $fee = $flat + ($outstanding * ($pct / 100));
        return number_format(max(0, $fee), 2, '.', '');
    }
}
