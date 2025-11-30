<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'family_id' => $this->family_id,
            'person_type' => $this->person_type,
            'bsa_member_id' => $this->bsa_member_id,
            'prefix' => $this->prefix,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'full_name' => $this->full_name,
            'nickname' => $this->nickname,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->age,
            'email' => $this->email,
            'phone' => $this->phone,
            'family' => new FamilyResource($this->whenLoaded('family')),
            'scout' => new ScoutResource($this->whenLoaded('scout')),
            'leader' => new AdultLeaderResource($this->whenLoaded('leader')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
