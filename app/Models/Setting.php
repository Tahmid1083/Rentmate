<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_email',
        'phone',
        'currency',
        'timezone',
        'locale',
        'late_fee_grace_days',
        'late_fee_flat',
        'late_fee_percent',
        'notify_invoice_created',
        'notify_payment_received',
        'notify_maintenance_status',
        'notify_lease_expiring',
        'notify_overdue_reminder',
    ];

    protected $casts = [
        'late_fee_grace_days'       => 'integer',
        'late_fee_flat'             => 'decimal:2',
        'late_fee_percent'          => 'decimal:2',
        'notify_invoice_created'    => 'boolean',
        'notify_payment_received'   => 'boolean',
        'notify_maintenance_status' => 'boolean',
        'notify_lease_expiring'     => 'boolean',
        'notify_overdue_reminder'   => 'boolean',
    ];

    /**
     * Default attribute values used when no row exists yet.
     */
    public static function defaults(): array
    {
        return [
            'business_name'             => null,
            'business_email'            => null,
            'phone'                     => null,
            'currency'                  => 'USD',
            'timezone'                  => 'UTC',
            'locale'                    => 'en',
            'late_fee_grace_days'       => 5,
            'late_fee_flat'             => '25.00',
            'late_fee_percent'          => '0.00',
            'notify_invoice_created'    => true,
            'notify_payment_received'   => true,
            'notify_maintenance_status' => true,
            'notify_lease_expiring'     => true,
            'notify_overdue_reminder'   => true,
        ];
    }

    /**
     * Get-or-create the settings row for a user, ensuring defaults are saved.
     */
    public static function for(User $user): static
    {
        return static::firstOrCreate(
            ['user_id' => $user->id],
            static::defaults()
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
