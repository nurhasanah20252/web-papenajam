@php
    use App\Models\JoomlaMigration;
    use App\Models\JoomlaMigrationItem;

    $migration = $getRecord();
    $itemsByType = JoomlaMigrationItem::where('migration_id', $migration->id)
        ->select('type', 'status')
        ->selectRaw('COUNT(*) as count')
        ->groupBy('type', 'status')
        ->get()
        ->groupBy('type');
@endphp

<div class="space-y-6">
    {{-- Migration Info --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500">Name</p>
            <p class="font-medium">{{ $migration->name }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Status</p>
            <x-filament::badge :color="$migration->status === 'completed' ? 'success' : ($migration->status === 'failed' ? 'danger' : 'info')">
                {{ $migration->status }}
            </x-filament::badge>
        </div>
        <div>
            <p class="text-sm text-gray-500">Started</p>
            <p class="font-medium">{{ $migration->started_at?->format('Y-m-d H:i:s') ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Completed</p>
            <p class="font-medium">{{ $migration->completed_at?->format('Y-m-d H:i:s') ?? '-' }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Records</p>
            <p class="font-medium">{{ $migration->total_records }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500">Progress</p>
            <p class="font-medium">{{ $migration->progress }}%</p>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="w-full bg-gray-200 rounded-full h-4">
        <div
            class="bg-primary-600 h-4 rounded-full"
            style="width: {{ $migration->progress }}%"
        ></div>
    </div>

    {{-- Results by Type --}}
    <div>
        <h4 class="font-medium text-gray-900 mb-3">Results by Type</h4>
        <div class="space-y-2">
            @php
                $types = [
                    'categories' => 'Categories',
                    'pages' => 'Pages',
                    'news' => 'News',
                    'menus' => 'Menus',
                    'documents' => 'Documents',
                ];
            @endphp

            @foreach($types as $type => $label)
                @php
                    $typeItems = $itemsByType->get($type, collect());
                    $completed = $typeItems->where('status', 'completed')->sum('count');
                    $failed = $typeItems->where('status', 'failed')->sum('count');
                    $skipped = $typeItems->where('status', 'skipped')->sum('count');
                    $total = $completed + $failed + $skipped;
                @endphp

                @if($total > 0)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium">{{ $label }}</p>
                            <p class="text-sm text-gray-500">Total: {{ $total }}</p>
                        </div>
                        <div class="flex space-x-4 text-sm">
                            <span class="text-green-600">{{ $completed }} completed</span>
                            <span class="text-red-600">{{ $failed }} failed</span>
                            <span class="text-gray-500">{{ $skipped }} skipped</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Recent Errors --}}
    @if(!empty($migration->errors) && count($migration->errors) > 0)
        <div>
            <h4 class="font-medium text-gray-900 mb-3">Recent Errors</h4>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($migration->errors as $error)
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800">
                            @if(isset($error['joomla_id']))
                                <strong>ID: {{ $error['joomla_id'] }}</strong> -
                            @endif
                            {{ $error['message'] ?? $error }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
