<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdultLeaderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'positions' => $this->positions,
            'ypt_status' => $this->ypt_status,
            'ypt_completion_date' => $this->ypt_completion_date,
            'ypt_expiration_date' => $this->ypt_expiration_date,
            'registration_expiration_date' => $this->registration_expiration_date,
            'days_until_ypt_expiration' => $this->days_until_ypt_expiration,
            'ypt_status_formatted' => $this->ypt_status_formatted,
            'person' => new PersonResource($this->whenLoaded('person')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
