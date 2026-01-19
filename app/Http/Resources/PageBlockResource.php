<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\PageBlock
 */
class PageBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'content' => $this->content,
            'settings' => $this->settings,
            'order' => $this->order,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'children' => PageBlockResource::collection($this->whenLoaded('children')),
        ];
    }
}
