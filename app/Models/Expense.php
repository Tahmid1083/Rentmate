<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'property_id',
        'unit_id',
        'category',
        'vendor',
        'description',
        'incurred_on',
        'amount',
        'is_recurring',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'incurred_on' => 'date',
        'is_recurring' => 'boolean',
    ];

    public const CATEGORY_MORTGAGE      = 'mortgage';
    public const CATEGORY_TAXES          = 'taxes';
    public const CATEGORY_INSURANCE      = 'insurance';
    public const CATEGORY_UTILITIES      = 'utilities';
    public const CATEGORY_REPAIR         = 'repair';
    public const CATEGORY_MANAGEMENT_FEE = 'management_fee';
    public const CATEGORY_OTHER          = 'other';

    public const CATEGORIES = [
        self::CATEGORY_MORTGAGE      => 'Mortgage',
        self::CATEGORY_TAXES          => 'Property taxes',
        self::CATEGORY_INSURANCE      => 'Insurance',
        self::CATEGORY_UTILITIES      => 'Utilities',
        self::CATEGORY_REPAIR         => 'Repairs & maintenance',
        self::CATEGORY_MANAGEMENT_FEE => 'Management fees',
        self::CATEGORY_OTHER          => 'Other',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
