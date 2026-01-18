<?php

namespace App\Resources\Sipp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sipp_case_id' => $this->sipp_case_id,
            'case_number' => $this->case_number,
            'case_title' => $this->case_title,
            'case_type' => $this->case_type,
            'registration_date' => $this->registration_date?->toDateString(),
            'closing_date' => $this->closing_date?->toDateString(),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'judge_name' => $this->judge_name,
            'plaintiff' => $this->plaintiff,
            'defendant' => $this->defendant,
            'claim_amount' => $this->claim_amount,
            'claim_amount_formatted' => $this->formatted_claim_amount,
            'decision' => $this->decision,
            'sync_status' => $this->sync_status,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
        ];
    }
}
