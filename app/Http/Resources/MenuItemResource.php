<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\MenuItem
 */
class MenuItemResource extends JsonResource
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
            'menu_id' => $this->menu_id,
            'title' => $this->title,
            'url' => $this->getUrl(),
            'url_type' => $this->url_type->value,
            'url_type_label' => $this->url_type->label(),
            'route_name' => $this->route_name,
            'page' => $this->whenLoaded('page', fn () => [
                'id' => $this->page->id,
                'title' => $this->page->title,
                'slug' => $this->page->slug,
            ]),
            'page_id' => $this->page_id,
            'custom_url' => $this->custom_url,
            'icon' => $this->icon,
            'order' => $this->order,
            'target_blank' => $this->target_blank,
            'is_active' => $this->is_active,
            'conditions' => $this->conditions,
            'parent_id' => $this->parent_id,
            'depth' => $this->when($request->input('include_depth'), fn () => $this->calculateDepth()),
            'has_children' => $this->hasChildren(),
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
