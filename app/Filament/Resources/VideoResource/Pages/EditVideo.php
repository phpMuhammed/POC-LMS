<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideo extends EditRecord
{
    protected static string $resource = VideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit_chapters')
                ->label('Edit Chapters')
                ->icon('heroicon-o-pencil-square')
                ->url(fn () => Pages\EditVideoChapters::getUrl(['record' => $this->record->id])),
            Actions\DeleteAction::make(),
        ];
    }
}
