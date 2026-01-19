<x-filament-panels::page>
    <div class="space-y-6">
        @if($this->isRunning)
            <x-filament::banner color="primary">
                Migration jobs have been queued. You can monitor progress in the table below.
            </x-filament::banner>
        @endif

        <!-- Migration Summary -->
        @if(!empty($this->getRecentMigrations()))
            <x-filament::section>
                <x-slot name="heading">
                    Recent Migrations
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->getRecentMigrations() as $migration)
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-sm truncate mr-2" title="{{ $migration['name'] }}">
                                    {{ $migration['name'] }}
                                </h3>
                                <x-filament::badge :color="match($migration['status']) {
                                    'completed' => 'success',
                                    'running' => 'info',
                                    'failed' => 'danger',
                                    'rolled_back' => 'warning',
                                    default => 'gray'
                                }">
                                    {{ ucfirst($migration['status']) }}
                                </x-filament::badge>
                            </div>

                            <div class="space-y-2 mt-3">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-primary-600 h-1.5 rounded-full" style="width: {{ $migration['progress'] }}%"></div>
                                </div>

                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>{{ $migration['progress'] }}% Complete</span>
                                    <span>{{ $migration['processed_records'] }} / {{ $migration['total_records'] }}</span>
                                </div>

                                @if($migration['failed_records'] > 0)
                                    <div class="text-xs text-red-500 font-medium">
                                        {{ $migration['failed_records'] }} failures detected
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <!-- Migration Controls Form -->
        <x-filament::section>
            <x-slot name="heading">
                Upload & Start Migrations
            </x-slot>

            <div class="space-y-6" wire:poll.5s>
                {{-- Form --}}
                <div class="grid grid-cols-1 gap-6">
                    {{ $this->form }}
                </div>

                {{-- Action Buttons --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <x-filament::button
                        color="primary"
                        wire:click="startMigration('category')"
                        :disabled="!$this->categoryFile"
                        icon="heroicon-o-folder"
                    >
                        Migrate Categories
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="startMigration('content')"
                        :disabled="!$this->contentFile"
                        icon="heroicon-o-document-text"
                    >
                        Migrate Content
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="startMigration('news')"
                        :disabled="!$this->newsFile"
                        icon="heroicon-o-newspaper"
                    >
                        Migrate News
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="startMigration('menu')"
                        :disabled="!$this->menuFile"
                        icon="heroicon-o-bars-3"
                    >
                        Migrate Menus
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="startMigration('document')"
                        :disabled="!$this->documentFile"
                        icon="heroicon-o-paper-clip"
                    >
                        Migrate Documents
                    </x-filament::button>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-800">
                    <x-filament::button
                        color="success"
                        wire:click="validateMigration"
                        icon="heroicon-o-check-circle"
                    >
                        Run Data Validation
                    </x-filament::button>
                </div>

                {{-- Migration Order Info --}}
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2 text-sm">
                        Recommended Migration Order
                    </h4>
                    <ul class="list-disc list-inside space-y-1 text-xs text-blue-800 dark:text-blue-200">
                        <li><strong>Categories:</strong> Must be migrated first as other content types depend on them.</li>
                        <li><strong>Content & News:</strong> Depends on Categories for classification.</li>
                        <li><strong>Menus:</strong> Depends on Content (Pages/News) for linking.</li>
                        <li><strong>Validation:</strong> Run after completing all batches to ensure data integrity.</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>

        <!-- Migration Details Table -->
        <x-filament::section>
            <x-slot name="heading">
                Migration History & Progress
            </x-slot>

            <div wire:poll.5s>
                {{ $this->table }}
            </div>
        </x-filament::section>

        <!-- Latest Migration Output -->
        @if(isset($this->migrationStats['validation']['output']))
            <x-filament::section>
                <x-slot name="heading">
                    Validation Output ({{ $this->migrationStats['validation']['timestamp'] ?? 'Just now' }})
                </x-slot>

                <div class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto shadow-inner border border-gray-700">
                    <pre class="text-xs font-mono whitespace-pre-wrap leading-relaxed">{{ $this->migrationStats['validation']['output'] }}</pre>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
