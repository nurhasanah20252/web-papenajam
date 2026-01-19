<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['file_path']) && $data['file_path'] !== $this->record->file_path) {
            $path = Storage::disk('public')->path($data['file_path']);

            if (file_exists($path)) {
                $data['file_size'] = filesize($path);
                $data['mime_type'] = mime_content_type($path);
                $data['checksum'] = hash_file('sha256', $path);
                $data['file_name'] = basename($data['file_path']);
                $data['file_type'] = pathinfo($path, PATHINFO_EXTENSION);
            }
        }

        return $data;
    }
}
