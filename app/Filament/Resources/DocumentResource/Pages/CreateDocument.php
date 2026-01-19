<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = Auth::id();

        if (! empty($data['file_path'])) {
            $path = Storage::disk('public')->path($data['file_path']);

            if (file_exists($path)) {
                $data['file_size'] = filesize($path);
                $data['mime_type'] = mime_content_type($path);
                $data['checksum'] = hash_file('sha256', $path);
                $data['file_name'] = basename($data['file_path']);
                $data['file_type'] = pathinfo($path, PATHINFO_EXTENSION);
            }
        }

        if (empty($data['version'])) {
            $data['version'] = '1.0';
        }

        return $data;
    }
}
