<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdultLeader extends Model
{
    protected $fillable = [
        'person_id',
        'positions',
        'ypt_status',
        'ypt_completion_date',
        'ypt_expiration_date',
        'registration_expiration_date',
    ];

    protected $casts = [
        'positions' => 'array',
        'ypt_completion_date' => 'date',
        'ypt_expiration_date' => 'date',
        'registration_expiration_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    // Accessors
    public function getDaysUntilYptExpirationAttribute(): ?int
    {
        if (!$this->ypt_expiration_date) {
            return null;
        }

        return now()->diffInDays($this->ypt_expiration_date, false);
    }

    public function getYptStatusFormattedAttribute(): string
    {
        $days = $this->days_until_ypt_expiration;

        if ($days === null) {
            return 'unknown';
        }

        if ($days < 0) {
            return 'expired';
        }

        if ($days < 30) {
            return 'expiring_soon';
        }

        if ($days < 90) {
            return 'expiring_in_90';
        }

        return 'current';
    }
}
