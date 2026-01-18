@php
    use Filament\Support\Facades\FilamentView;
    use Filament\Pages\SimplePage;
    use Filament\Support\Concerns\EvaluatesClosures;
    use Filament\Support\Concerns\HasContent;
    use Filament\Support\Concerns\HasHeading;
    use Filament\Support\Concerns\HasSubheading;
    use Filament\Support\Concerns\HasTitle;
    use Filament\Support\Facades\filament;
    use Illuminate\Support\Facades\Blade;
    use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;

    $heading = $getHeading();
    $subheading = $getSubheading();
    $title = $getTitle();
@endphp

<x-filament-panels::page.simple>
    <x-filament-panels::header.simple
        :heading="$heading"
        :subheading="$subheading"
        :title="$title"
    />

    <div class="space-y-6">
        {{-- Migration Form --}}
        <x-filament::card>
            <form wire:submit="startMigration">
                {{ $this->form }}

                <div class="mt-4 flex justify-end">
                    <x-filament::button
                        wire:loading.attr="disabled"
                        wire:target="startMigration"
                        type="submit"
                        :disabled="$isMigrating"
                    >
                        <span wire:loading.remove wire:target="startMigration">Start Migration</span>
                        <span wire:loading wire:target="startMigration">Migrating...</span>
                    </x-filament::button>
                </div>
            </form>
        </x-filament::card>

        {{-- Migration Progress --}}
        @if($isMigrating)
            <x-filament::card>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Migration Progress</span>
                        <span class="text-sm text-gray-500">{{ $currentProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div
                            class="bg-primary-600 h-2.5 rounded-full transition-all duration-300"
                            style="width: {{ $currentProgress }}%"
                        ></div>
                    </div>
                    <p class="text-sm text-gray-500">Processing records... please wait.</p>
                </div>
            </x-filament::card>
        @endif

        {{-- Migration History --}}
        <x-filament::card>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Migration History</h3>
            </div>

            {{ $this->table }}
        </x-filament::card>

        {{-- Help Section --}}
        <x-filament::card class="bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Data Format Requirements</h3>
            <div class="prose prose-sm text-gray-600">
                <p>The uploaded JSON file should have the following structure:</p>
                <pre class="bg-gray-100 p-3 rounded text-xs overflow-x-auto">
{
    "categories": [
        {"id": 1, "title": "Category Name", "alias": "category-alias", ...}
    ],
    "articles": [
        {"id": 1, "title": "Article Title", "alias": "article-alias", "introtext": "...", ...}
    ],
    "news": [
        {"id": 1, "title": "News Title", "alias": "news-alias", ...}
    ],
    "menus": [
        {"id": 1, "title": "Menu Name", "menutype": "mainmenu", ...}
    ],
    "menu_items": [
        {"id": 1, "title": "Menu Item", "link": "...", ...}
    ],
    "documents": [
        {"id": 1, "title": "Document", "filename": "path/to/file.pdf", ...}
    ]
}
                </pre>
                <p>All fields are optional except for <code>title</code> (or <code>name</code>) in each record.</p>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page.simple>
