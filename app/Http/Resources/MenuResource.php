<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Menu
 */
class MenuResource extends JsonResource
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
            'name' => $this->name,
            'location' => $this->location->value,
            'location_label' => $this->location->label(),
            'max_depth' => $this->max_depth,
            'description' => $this->description,
            'items_count' => $this->whenCounted('items'),
            'has_items' => $this->hasItems(),
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
            'tree' => $this->when($request->input('as_tree'), fn () => $this->getTree()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
