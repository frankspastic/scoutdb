<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'street_address',
        'city',
        'state',
        'zip',
        'primary_phone',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function persons(): HasMany
    {
        return $this->hasMany(Person::class)->whereNull('deleted_at');
    }

    public function scouts(): HasMany
    {
        return $this->hasMany(Person::class)
            ->where('person_type', 'scout')
            ->whereNull('deleted_at');
    }

    public function parents(): HasMany
    {
        return $this->hasMany(Person::class)
            ->where('person_type', 'parent')
            ->whereNull('deleted_at');
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(Person::class)
            ->where('person_type', 'sibling')
            ->whereNull('deleted_at');
    }

    public function leaders(): HasMany
    {
        return $this->hasMany(Person::class)
            ->where('person_type', 'adult_leader')
            ->whereNull('deleted_at');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
