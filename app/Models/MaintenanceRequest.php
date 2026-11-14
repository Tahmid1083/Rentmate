<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'unit_id',
        'property_id',
        'reporter_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'photo_path',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public const CATEGORY_PLUMBING     = 'plumbing';
    public const CATEGORY_ELECTRICAL   = 'electrical';
    public const CATEGORY_HVAC         = 'hvac';
    public const CATEGORY_APPLIANCE    = 'appliance';
    public const CATEGORY_STRUCTURAL   = 'structural';
    public const CATEGORY_PEST         = 'pest_control';
    public const CATEGORY_OTHER        = 'other';

    public const PRIORITY_LOW      = 'low';
    public const PRIORITY_MEDIUM   = 'medium';
    public const PRIORITY_HIGH     = 'high';
    public const PRIORITY_URGENT   = 'urgent';

    public const STATUS_OPEN          = 'open';
    public const STATUS_IN_PROGRESS   = 'in_progress';
    public const STATUS_ON_HOLD       = 'on_hold';
    public const STATUS_COMPLETED     = 'completed';
    public const STATUS_CANCELLED     = 'cancelled';

    public const CATEGORIES = [
        self::CATEGORY_PLUMBING   => 'Plumbing',
        self::CATEGORY_ELECTRICAL => 'Electrical',
        self::CATEGORY_HVAC       => 'HVAC',
        self::CATEGORY_APPLIANCE  => 'Appliance',
        self::CATEGORY_STRUCTURAL => 'Structural',
        self::CATEGORY_PEST       => 'Pest control',
        self::CATEGORY_OTHER      => 'Other',
    ];

    public const PRIORITIES = [
        self::PRIORITY_LOW    => 'Low',
        self::PRIORITY_MEDIUM => 'Medium',
        self::PRIORITY_HIGH   => 'High',
        self::PRIORITY_URGENT => 'Urgent',
    ];

    public const STATUSES = [
        self::STATUS_OPEN        => 'Open',
        self::STATUS_IN_PROGRESS => 'In progress',
        self::STATUS_ON_HOLD     => 'On hold',
        self::STATUS_COMPLETED   => 'Completed',
        self::STATUS_CANCELLED   => 'Cancelled',
    ];

    /**
     * The unit this request is for.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Convenience relation to the property.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The landlord (or future tenant) who reported it.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Timeline entries for status changes / notes.
     */
    public function timeline(): HasMany
    {
        return $this->hasMany(MaintenanceTimeline::class);
    }

    /**
     * Push a timeline entry (status change / note).
     */
    public function pushTimeline(string $event, ?string $note = null): MaintenanceTimeline
    {
        return $this->timeline()->create([
            'event'    => $event,
            'note'     => $note,
            'user_id'  => auth()->id(),
        ]);
    }
}
