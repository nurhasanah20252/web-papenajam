<?php

namespace App\Http\Controllers;

use App\Http\Requests\PageBuilder\UpdatePageBuilderRequest;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PageBuilderController extends Controller
{
    /**
     * Show the page builder editor.
     */
    public function edit(Page $page): Response
    {
        return Inertia::render('PageBuilder/Editor', [
            'page' => $page->load(['author', 'lastEditedBy', 'template']),
            'versions' => $page->versions()->with('creator')->get(),
            'templates' => PageTemplate::all(),
            'block_types' => $this->getBlockTypesData(),
        ]);
    }

    /**
     * Get page builder content.
     */
    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'page' => $page->load(['author', 'lastEditedBy', 'template']),
            'blocks' => $page->blocks()->orderBy('order')->get(),
            'builder_content' => $page->builder_content,
        ]);
    }

    /**
     * Save page builder content.
     */
    public function update(UpdatePageBuilderRequest $request, Page $page): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($page, $validated) {
            // Update page content
            $page->update([
                'builder_content' => $validated['builder_content'],
                'content' => $validated['html_content'] ?? $page->content, // html_content maps to content column
                'last_edited_by' => Auth::id(),
                'is_builder_enabled' => true,
            ]);

            // Create a new version snapshot
            $page->createVersion(Auth::id());

            return response()->json([
                'message' => 'Page saved successfully',
                'page' => $page->fresh(['author', 'lastEditedBy', 'template']),
                'version' => $page->version,
            ]);
        });
    }

    /**
     * Save current page layout as a reusable PageTemplate.
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'builder_content' => 'required|array',
            'thumbnail' => 'nullable|string',
        ]);

        $template = PageTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'content' => $validated['builder_content'],
            'thumbnail' => $validated['thumbnail'],
            'created_by' => Auth::id(),
            'is_system' => false,
        ]);

        return response()->json([
            'message' => 'Template saved successfully',
            'template' => $template,
        ]);
    }

    /**
     * Restore the page to a specific version.
     */
    public function restoreVersion(Page $page, $versionId): JsonResponse
    {
        $success = $page->restoreVersion((int) $versionId);

        if ($success) {
            return response()->json([
                'message' => 'Page restored to version successfully',
                'page' => $page->fresh(['author', 'lastEditedBy', 'template']),
                'version' => $page->version,
            ]);
        }

        return response()->json([
            'message' => 'Failed to restore version',
        ], 500);
    }

    /**
     * Duplicate a page block.
     */
    public function duplicateBlock(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:page_blocks,id',
        ]);

        $block = PageBlock::findOrFail($validated['block_id']);

        if ($block->page_id !== $page->id) {
            return response()->json(['message' => 'Block does not belong to this page'], 403);
        }

        $newBlock = $block->replicate();
        $newBlock->order = $block->order + 1;
        $newBlock->save();

        PageBlock::where('page_id', $page->id)
            ->where('order', '>=', $newBlock->order)
            ->where('id', '!=', $newBlock->id)
            ->increment('order');

        return response()->json([
            'message' => 'Block duplicated successfully',
            'block' => $newBlock,
        ]);
    }

    /**
     * Delete a page block.
     */
    public function deleteBlock(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:page_blocks,id',
        ]);

        $block = PageBlock::findOrFail($validated['block_id']);

        if ($block->page_id !== $page->id) {
            return response()->json(['message' => 'Block does not belong to this page'], 403);
        }

        $block->delete();

        return response()->json([
            'message' => 'Block deleted successfully',
        ]);
    }

    /**
     * Preview page builder content.
     */
    public function preview(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'builder_content' => 'required|array',
        ]);

        return response()->json([
            'html' => $this->renderBuilderContent($validated['builder_content']),
        ]);
    }

    /**
     * Get available block types.
     */
    public function blockTypes(): JsonResponse
    {
        return response()->json([
            'block_types' => $this->getBlockTypesData(),
        ]);
    }

    /**
     * Get block types data array.
     */
    protected function getBlockTypesData(): array
    {
        return [
            [
                'type' => 'text',
                'label' => 'Text',
                'icon' => 'align-left',
                'category' => 'basic',
                'description' => 'Add text content with rich formatting',
            ],
            [
                'type' => 'heading',
                'label' => 'Heading',
                'icon' => 'heading',
                'category' => 'basic',
                'description' => 'Add a heading with different sizes',
            ],
            [
                'type' => 'image',
                'label' => 'Image',
                'icon' => 'image',
                'category' => 'media',
                'description' => 'Add an image with optional caption',
            ],
            [
                'type' => 'gallery',
                'label' => 'Gallery',
                'icon' => 'photo',
                'category' => 'media',
                'description' => 'Add a grid of images',
            ],
            [
                'type' => 'video',
                'label' => 'Video',
                'icon' => 'video',
                'category' => 'media',
                'description' => 'Embed a video from YouTube or Vimeo',
            ],
            [
                'type' => 'columns',
                'label' => 'Columns',
                'icon' => 'columns',
                'category' => 'layout',
                'description' => 'Split content into multiple columns',
            ],
            [
                'type' => 'section',
                'label' => 'Section',
                'icon' => 'square',
                'category' => 'layout',
                'description' => 'Add a section with background color',
            ],
            [
                'type' => 'spacer',
                'label' => 'Spacer',
                'icon' => 'arrows-vertical',
                'category' => 'layout',
                'description' => 'Add vertical space between blocks',
            ],
            [
                'type' => 'separator',
                'label' => 'Separator',
                'icon' => 'minus',
                'category' => 'layout',
                'description' => 'Add a horizontal line',
            ],
            [
                'type' => 'html',
                'label' => 'HTML',
                'icon' => 'code',
                'category' => 'advanced',
                'description' => 'Add custom HTML code',
            ],
        ];
    }

    /**
     * Sync blocks for a page.
     */
    protected function syncBlocks(Page $page, array $blocks): void
    {
        $existingBlockIds = $page->blocks->pluck('id')->toArray();
        $incomingBlockIds = [];

        foreach ($blocks as $blockData) {
            if (isset($blockData['id'])) {
                $incomingBlockIds[] = $blockData['id'];

                PageBlock::where('id', $blockData['id'])->update([
                    'type' => $blockData['type'],
                    'content' => $blockData['content'],
                    'settings' => $blockData['settings'] ?? [],
                    'order' => $blockData['order'],
                    'parent_id' => $blockData['parent_id'] ?? null,
                ]);
            } else {
                $block = $page->blocks()->create([
                    'type' => $blockData['type'],
                    'content' => $blockData['content'],
                    'settings' => $blockData['settings'] ?? [],
                    'order' => $blockData['order'],
                    'parent_id' => $blockData['parent_id'] ?? null,
                ]);

                $incomingBlockIds[] = $block->id;
            }
        }

        $blocksToDelete = array_diff($existingBlockIds, $incomingBlockIds);
        if (! empty($blocksToDelete)) {
            PageBlock::whereIn('id', $blocksToDelete)->delete();
        }
    }

    /**
     * Render builder content to HTML.
     */
    protected function renderBuilderContent(array $content): string
    {
        $html = '';

        foreach ($content as $block) {
            $html .= $this->renderBlock($block);
        }

        return $html;
    }

    /**
     * Render a single block.
     */
    protected function renderBlock(array $block): string
    {
        return match ($block['type']) {
            'text' => '<div class="prose">'.$block['content']['text'] ?? ''.'</div>',
            'heading' => sprintf(
                '<h%s class="%s">%s</h%s>',
                $block['content']['level'] ?? 2,
                $block['settings']['css_class'] ?? '',
                $block['content']['text'] ?? '',
                $block['content']['level'] ?? 2
            ),
            'image' => sprintf(
                '<img src="%s" alt="%s" class="%s" %s>',
                $block['content']['url'] ?? '',
                $block['content']['alt'] ?? '',
                $block['settings']['css_class'] ?? 'img-fluid',
                $block['content']['width'] ? 'width="'.$block['content']['width'].'"' : ''
            ),
            default => '',
        };
    }
}
