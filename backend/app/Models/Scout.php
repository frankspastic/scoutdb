<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scout extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'person_id',
        'grade',
        'rank',
        'den',
        'registration_expiration_date',
        'registration_status',
        'ypt_status',
        'program',
    ];

    protected $casts = [
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
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->registration_expiration_date) {
            return null;
        }

        return now()->diffInDays($this->registration_expiration_date, false);
    }

    public function getExpirationStatusAttribute(): string
    {
        $days = $this->days_until_expiration;

        if ($days === null) {
            return 'unknown';
        }

        if ($days < 0) {
            return 'expired';
        }

        if ($days < 30) {
            return 'expiring_soon';
        }

        if ($days < 60) {
            return 'expiring_in_60';
        }

        return 'active';
    }
}
