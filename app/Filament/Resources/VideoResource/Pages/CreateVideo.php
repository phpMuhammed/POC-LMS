<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideo extends CreateRecord
{
    protected static string $resource = VideoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract file metadata if file_path is set
        if (isset($data['file_path']) && is_array($data['file_path'])) {
            $filePath = $data['file_path'][0] ?? null;
            if ($filePath) {
                $data['file_path'] = $filePath;
                $fullPath = storage_path('app/public/'.$filePath);
                if (file_exists($fullPath)) {
                    $data['file_name'] = basename($filePath);
                    $data['file_size'] = filesize($fullPath);
                    $data['mime_type'] = mime_content_type($fullPath);
                }
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit-chapters', ['record' => $this->record->id]);
    }
}
