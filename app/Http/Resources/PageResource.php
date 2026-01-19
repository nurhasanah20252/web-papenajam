<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Page
 */
class PageResource extends JsonResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'meta' => $this->meta,
            'featured_image' => $this->featured_image,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'page_type' => $this->page_type->value,
            'page_type_label' => $this->page_type->label(),
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
            ]),
            'last_edited_by' => $this->whenLoaded('lastEditedBy', fn () => [
                'id' => $this->lastEditedBy->id,
                'name' => $this->lastEditedBy->name,
            ]),
            'template' => $this->whenLoaded('template', fn () => [
                'id' => $this->template->id,
                'name' => $this->template->name,
            ]),
            'template_id' => $this->template_id,
            'published_at' => $this->published_at?->toISOString(),
            'view_count' => $this->view_count,
            'builder_content' => $this->builder_content,
            'version' => $this->version,
            'is_builder_enabled' => $this->is_builder_enabled,
            'url' => $this->getUrl(),
            'is_published' => $this->isPublished(),
            'is_draft' => $this->isDraft(),
            'blocks_count' => $this->whenCounted('blocks'),
            'blocks' => PageBlockResource::collection($this->whenLoaded('blocks')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
