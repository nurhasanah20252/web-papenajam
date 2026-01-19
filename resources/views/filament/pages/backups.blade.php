<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Existing Backups
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-start divide-y divide-gray-200 dark:divide-white/5">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5">
                        <th class="px-4 py-3 text-start text-sm font-semibold text-gray-900 dark:text-white">Filename</th>
                        <th class="px-4 py-3 text-start text-sm font-semibold text-gray-900 dark:text-white">Size</th>
                        <th class="px-4 py-3 text-start text-sm font-semibold text-gray-900 dark:text-white">Date</th>
                        <th class="px-4 py-3 text-end text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-950 dark:text-white">
                                {{ $backup['filename'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-950 dark:text-white">
                                {{ $backup['size'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-950 dark:text-white">
                                {{ $backup['date'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-end">
                                <div class="flex justify-end gap-x-3">
                                    <x-filament::button
                                        wire:click="downloadBackup('{{ $backup['filename'] }}')"
                                        color="gray"
                                        size="sm"
                                        icon="heroicon-m-arrow-down-tray"
                                    >
                                        Download
                                    </x-filament::button>

                                    <x-filament::button
                                        wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                        wire:confirm="Are you sure you want to delete this backup?"
                                        color="danger"
                                        size="sm"
                                        icon="heroicon-m-trash"
                                    >
                                        Delete
                                    </x-filament::button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                No backups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
