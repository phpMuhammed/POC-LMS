<?php

namespace App\Filament\Resources\VideoResource\Pages;

use App\Filament\Resources\VideoResource;
use App\Models\Video;
use App\Models\VideoChapter;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class EditVideoChapters extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = VideoResource::class;

    protected string $view = 'filament.resources.video-resource.pages.edit-video-chapters';

    public array $chapters = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->load('chapters');

        $this->chapters = $this->record->chapters->map(fn ($chapter) => [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'start_time' => $chapter->start_time,
            'end_time' => $chapter->end_time,
            'order' => $chapter->order,
        ])->toArray();
    }

    protected function resolveRecord(int|string $key): Video
    {
        return Video::with('chapters')->findOrFail($key);
    }

    public function getTitle(): string
    {
        return 'Edit Chapters: '.$this->record?->title ?? 'Video Chapters';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(VideoChapter::query()->where('video_id', $this->record->id))
            ->columns([
                TextColumn::make('order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Start')
                    ->formatStateUsing(fn ($state) => gmdate('H:i:s', (int) $state))
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('End')
                    ->formatStateUsing(fn ($state) => gmdate('H:i:s', (int) $state))
                    ->sortable(),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->getStateUsing(fn (VideoChapter $record) => $record->end_time - $record->start_time)
                    ->formatStateUsing(fn ($state) => gmdate('H:i:s', (int) $state)),
            ])
            ->actions([
                Actions\Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('start_time')
                            ->label('Start Time (seconds)')
                            ->numeric()
                            ->required(),
                        TextInput::make('end_time')
                            ->label('End Time (seconds)')
                            ->numeric()
                            ->required(),
                    ])
                    ->fillForm(fn (VideoChapter $record) => [
                        'title' => $record->title,
                        'start_time' => $record->start_time,
                        'end_time' => $record->end_time,
                    ])
                    ->action(function (VideoChapter $record, array $data): void {
                        $record->update($data);
                        Notification::make()
                            ->title('Chapter updated successfully')
                            ->success()
                            ->send();
                        $this->record->refresh();
                        $this->record->load('chapters');
                        $this->chapters = $this->record->chapters->map(fn ($chapter) => [
                            'id' => $chapter->id,
                            'title' => $chapter->title,
                            'start_time' => $chapter->start_time,
                            'end_time' => $chapter->end_time,
                            'order' => $chapter->order,
                        ])->toArray();
                    }),
                Actions\Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (VideoChapter $record): void {
                        $record->delete();
                        Notification::make()
                            ->title('Chapter deleted successfully')
                            ->success()
                            ->send();
                        $this->record->refresh();
                        $this->record->load('chapters');
                        $this->chapters = $this->record->chapters->map(fn ($chapter) => [
                            'id' => $chapter->id,
                            'title' => $chapter->title,
                            'start_time' => $chapter->start_time,
                            'end_time' => $chapter->end_time,
                            'order' => $chapter->order,
                        ])->toArray();
                    }),
            ])
            ->defaultSort('order');
    }

    public function addChapter(float $startTime, float $endTime): void
    {
        VideoChapter::create([
            'video_id' => $this->record->id,
            'title' => 'New Chapter',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'order' => (VideoChapter::where('video_id', $this->record->id)->max('order') ?? 0) + 1,
        ]);

        Notification::make()
            ->title('Chapter added successfully')
            ->success()
            ->send();

        // Refresh the record and chapters
        $this->record->refresh();
        $this->record->load('chapters');
        $this->chapters = $this->record->chapters->map(fn ($chapter) => [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'start_time' => $chapter->start_time,
            'end_time' => $chapter->end_time,
            'order' => $chapter->order,
        ])->toArray();
    }

    public function updateChapterTitle(int $chapterId, string $title): void
    {
        $chapter = VideoChapter::findOrFail($chapterId);
        $chapter->update(['title' => $title]);

        Notification::make()
            ->title('Chapter title updated')
            ->success()
            ->send();

        $this->record->refresh();
        $this->record->load('chapters');
        $this->chapters = $this->record->chapters->map(fn ($chapter) => [
            'id' => $chapter->id,
            'title' => $chapter->title,
            'start_time' => $chapter->start_time,
            'end_time' => $chapter->end_time,
            'order' => $chapter->order,
        ])->toArray();
    }

    public function getVideoUrl(): string
    {
        if (! $this->record) {
            return '';
        }

        if (str_starts_with($this->record->file_path, 'http')) {
            return $this->record->file_path;
        }

        return asset('storage/'.$this->record->file_path);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Back to Videos')
                ->url(VideoResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
