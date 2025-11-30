<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wordpress_user_id' => $this->wordpress_user_id,
            'person_id' => $this->person_id,
            'role' => $this->role,
            'granted_by' => $this->granted_by,
            'granted_at' => $this->granted_at,
            'person' => new PersonResource($this->whenLoaded('person')),
            'granted_by_permission' => new UserPermissionResource($this->whenLoaded('grantedBy')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
