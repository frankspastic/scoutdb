<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SyncLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sync_type' => $this->sync_type,
            'status' => $this->status,
            'records_processed' => $this->records_processed,
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
