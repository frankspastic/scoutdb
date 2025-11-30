<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_id',
        'bsa_member_id',
        'person_type',
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'nickname',
        'gender',
        'date_of_birth',
        'age',
        'email',
        'phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function scout(): HasOne
    {
        return $this->hasOne(Scout::class);
    }

    public function leader(): HasOne
    {
        return $this->hasOne(AdultLeader::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->prefix,
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ]);

        return implode(' ', $parts);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('person_type', $type);
    }

    public function scopeScouts($query)
    {
        return $query->where('person_type', 'scout');
    }

    public function scopeParents($query)
    {
        return $query->where('person_type', 'parent');
    }

    public function scopeSiblings($query)
    {
        return $query->where('person_type', 'sibling');
    }

    public function scopeLeaders($query)
    {
        return $query->where('person_type', 'adult_leader');
    }

    public function scopeOrphaned($query)
    {
        return $query->whereNull('family_id');
    }
}
