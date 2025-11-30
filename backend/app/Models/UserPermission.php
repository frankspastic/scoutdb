<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    protected $fillable = [
        'wordpress_user_id',
        'person_id',
        'role',
        'granted_by',
        'granted_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(UserPermission::class, 'granted_by');
    }
}
