<?php

namespace App\Services\PageBuilder;

use App\Enums\BlockType;
use Illuminate\Support\Collection;

class BlockRenderer
{
    /**
     * Render a collection of blocks.
     */
    public function render(array|Collection $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $html .= $this->renderBlock($block);
        }

        return $html;
    }

    /**
     * Render a single block.
     */
    public function renderBlock(array|\App\Models\PageBlock $block): string
    {
        $block = is_array($block) ? $block : $block->toArray();
        $type = $block['type'] instanceof BlockType ? $block['type']->value : $block['type'];

        $attributes = $this->getBlockAttributes($block);

        $content = match ($type) {
            'section' => $this->renderSection($block),
            'columns' => $this->renderColumns($block),
            'text' => $this->renderText($block),
            'heading' => $this->renderHeading($block),
            'image' => $this->renderImage($block),
            'video' => $this->renderVideo($block),
            'spacer' => $this->renderSpacer($block),
            'separator' => $this->renderSeparator($block),
            'html' => $this->renderHtml($block),
            default => "<!-- Block type {$type} not implemented -->",
        };

        if (in_array($type, ['section', 'columns'])) {
            return $content;
        }

        return sprintf(
            '<div class="block block-%s %s" %s>%s</div>',
            $type,
            $block['css_class'] ?? '',
            $block['anchor_id'] ? 'id="'.$block['anchor_id'].'"' : '',
            $content
        );
    }

    protected function getBlockAttributes(array $block): string
    {
        $attrs = [];
        if (! empty($block['anchor_id'])) {
            $attrs[] = 'id="'.e($block['anchor_id']).'"';
        }

        $classes = ['block', 'block-'.$block['type']];
        if (! empty($block['css_class'])) {
            $classes[] = $block['css_class'];
        }

        $attrs[] = 'class="'.implode(' ', array_map('e', $classes)).'"';

        return implode(' ', $attrs);
    }

    protected function renderSection(array $block): string
    {
        $children = $block['children'] ?? [];
        $content = $this->render($children);

        $style = '';
        if (! empty($block['settings']['background_color'])) {
            $style .= 'background-color: '.$block['settings']['background_color'].';';
        }

        return sprintf(
            '<section class="py-12 %s" %s style="%s"><div class="container mx-auto px-4">%s</div></section>',
            $block['css_class'] ?? '',
            $block['anchor_id'] ? 'id="'.$block['anchor_id'].'"' : '',
            $style,
            $content
        );
    }

    protected function renderColumns(array $block): string
    {
        $columns = $block['content']['columns'] ?? [];
        $html = '<div class="flex flex-wrap -mx-4">';

        foreach ($columns as $column) {
            $width = $column['width'] ?? '1/2';
            $html .= sprintf(
                '<div class="px-4 w-full md:w-%s">%s</div>',
                $width,
                $this->render($column['blocks'] ?? [])
            );
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderText(array $block): string
    {
        return '<div class="prose max-w-none">'.($block['content']['text'] ?? '').'</div>';
    }

    protected function renderHeading(array $block): string
    {
        $level = $block['content']['level'] ?? 2;

        return sprintf(
            '<h%d class="font-bold mb-4">%s</h%d>',
            $level,
            e($block['content']['text'] ?? ''),
            $level
        );
    }

    protected function renderImage(array $block): string
    {
        $url = $block['content']['url'] ?? '';
        $alt = $block['content']['alt'] ?? '';
        $caption = $block['content']['caption'] ?? '';

        $html = sprintf('<img src="%s" alt="%s" class="max-w-full h-auto rounded shadow">', e($url), e($alt));

        if ($caption) {
            $html .= '<p class="text-sm text-gray-500 mt-2">'.e($caption).'</p>';
        }

        return $html;
    }

    protected function renderVideo(array $block): string
    {
        $url = $block['content']['url'] ?? '';

        // Basic implementation for YouTube/Vimeo
        return '<div class="aspect-video"><iframe src="'.e($url).'" class="w-full h-full" allowfullscreen></iframe></div>';
    }

    protected function renderSpacer(array $block): string
    {
        $height = $block['settings']['height'] ?? '2rem';

        return '<div style="height: '.e($height).'"></div>';
    }

    protected function renderSeparator(array $block): string
    {
        return '<hr class="my-8 border-t border-gray-200">';
    }

    protected function renderHtml(array $block): string
    {
        return $block['content']['html'] ?? '';
    }
}
