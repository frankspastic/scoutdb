<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'grade' => $this->grade,
            'rank' => $this->rank,
            'den' => $this->den,
            'registration_expiration_date' => $this->registration_expiration_date,
            'registration_status' => $this->registration_status,
            'ypt_status' => $this->ypt_status,
            'program' => $this->program,
            'days_until_expiration' => $this->days_until_expiration,
            'expiration_status' => $this->expiration_status,
            'person' => new PersonResource($this->whenLoaded('person')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
