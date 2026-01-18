<?php

namespace App\Resources\Sipp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sipp_case_id' => $this->sipp_case_id,
            'case_number' => $this->case_number,
            'case_title' => $this->case_title,
            'case_type' => $this->case_type,
            'judge_name' => $this->judge_name,
            'court_room' => $this->court_room,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'scheduled_time' => $this->formatted_time,
            'scheduled_datetime' => $this->scheduled_date && $this->scheduled_time
                ? $this->scheduled_date->toIso8601String()
                : null,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'agenda' => $this->agenda,
            'notes' => $this->notes,
            'sync_status' => $this->sync_status,
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
        ];
    }
}
