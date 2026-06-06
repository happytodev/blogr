<?php

namespace Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource;

class EditBlogSeries extends EditRecord
{
    protected static string $resource = BlogSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('managePostsOrder')
                ->label('Reorder Posts')
                ->icon('heroicon-o-arrows-up-down')
                ->modalWidth('2xl')
                ->modalHeading(fn () => 'Reorder posts in "' . $this->record->title . '"')
                ->modalDescription('Drag posts to reorder them within the series.')
                ->form(fn () => [
                    \Filament\Forms\Components\Repeater::make('orderedPosts')
                        ->label('Posts')
                        ->schema([
                            \Filament\Forms\Components\Hidden::make('id'),
                            \Filament\Forms\Components\TextInput::make('display_title')
                                ->label('Post')
                                ->disabled()
                                ->extraAttributes(['class' => 'border-0 bg-transparent']),
                        ])
                        ->orderable()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderableWithDragAndDrop(true)
                        ->defaultItems(0)
                        ->default(function () {
                            return $this->record->posts()
                                ->with('translations')
                                ->orderBy('series_position')
                                ->get()
                                ->map(fn ($post) => [
                                    'id' => $post->id,
                                    'display_title' => $post->translations->first()?->title ?? "Post #{$post->id}",
                                ])
                                ->toArray();
                        }),
                ])
                ->action(function (array $data) {
                    foreach ($data['orderedPosts'] as $index => $item) {
                        BlogPost::where('id', $item['id'])
                            ->update(['series_position' => $index + 1]);
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
