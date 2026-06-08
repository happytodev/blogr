<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Services\CmsPageImportExportService;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class ListCmsPages extends ListRecords
{
    use WithFileUploads;

    protected static string $resource = CmsPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('importJson')
                ->label('Import JSON')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->form([
                    FileUpload::make('file')
                        ->label('JSON or ZIP file')
                        ->acceptedFileTypes(['application/json', 'application/zip', 'text/plain'])
                        ->maxSize(10240)
                        ->disk('local')
                        ->directory('temp-cms-import')
                        ->visibility('private')
                        ->helperText('Upload a .json or .zip file exported from a CMS page'),

                    Select::make('on_conflict')
                        ->label('If slug already exists')
                        ->options([
                            'new' => 'Create a new page (unique slug)',
                            'replace' => 'Replace existing page',
                            'skip' => 'Skip import',
                        ])
                        ->default('new')
                        ->helperText('What to do if a page with the same slug already exists'),
                ])
                ->modalHeading('Import CMS Page')
                ->modalSubmitActionLabel('Import')
                ->action(function (array $data) {
                    $file = $data['file'] ?? null;

                    if (empty($file)) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Please select a file to import.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $filePath = null;

                    if (is_array($file)) {
                        $file = reset($file);
                    }

                    if ($file instanceof TemporaryUploadedFile) {
                        $filePath = $file->getRealPath();
                    } elseif (is_string($file)) {
                        $filePath = Storage::disk('local')->path($file);
                    }

                    if (! $filePath || ! file_exists($filePath)) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Could not read the uploaded file.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $service = app(CmsPageImportExportService::class);
                        $page = $service->importFromFile($filePath, $data['on_conflict'] ?? 'new');

                        $translation = $page->translations->first();
                        $title = $translation?->title ?? $page->slug;

                        Notification::make()
                            ->title("Page '{$title}' imported successfully")
                            ->success()
                            ->send();

                        $this->resetTable();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('Import skipped')
                            ->body($e->getMessage())
                            ->warning()
                            ->send();
                    } catch (\InvalidArgumentException $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('An unexpected error occurred: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
