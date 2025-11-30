<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'primary_phone' => $this->primary_phone,
            'notes' => $this->notes,
            'persons' => PersonResource::collection($this->whenLoaded('persons')),
            'scouts' => PersonResource::collection($this->whenLoaded('scouts')),
            'parents' => PersonResource::collection($this->whenLoaded('parents')),
            'siblings' => PersonResource::collection($this->whenLoaded('siblings')),
            'leaders' => PersonResource::collection($this->whenLoaded('leaders')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
