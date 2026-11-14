<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rent_invoice_id',
        'amount',
        'paid_on',
        'method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_on' => 'date',
    ];

    public const METHODS = [
        'cash'      => 'Cash',
        'check'     => 'Check',
        'bank'      => 'Bank transfer',
        'card'      => 'Card',
        'online'    => 'Online (Stripe/PayPal)',
        'other'     => 'Other',
    ];

    /**
     * The invoice this payment was applied to.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(RentInvoice::class, 'rent_invoice_id');
    }
}
