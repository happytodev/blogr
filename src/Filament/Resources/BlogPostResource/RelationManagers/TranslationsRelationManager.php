<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\RelationManagers;

use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';
    protected static ?string $title = 'Translations';
    protected static string | BackedEnum | null $icon = 'heroicon-o-language';

    public function form(Schema $schema): Schema
    {
        $availableLocales = config('blogr.locales.available', ['en', 'fr', 'es', 'de']);
        
        $localeNames = [
            'en' => 'English', 'fr' => 'Francais', 'es' => 'Espanol',
            'de' => 'Deutsch', 'it' => 'Italiano', 'pt' => 'Portugues',
            'nl' => 'Nederlands', 'ru' => 'Russian', 'ja' => 'Japanese',
            'zh' => 'Chinese', 'ar' => 'Arabic', 'hi' => 'Hindi',
        ];
        
        $localeOptions = [];
        foreach ($availableLocales as $locale) {
            $localeOptions[$locale] = $localeNames[$locale] ?? strtoupper($locale);
        }

        return $schema->schema([
            Forms\Components\Select::make('locale')
                ->label('Language')
                ->options($localeOptions)
                ->required()
                ->disabled(fn (?Model $record) => $record !== null)
                ->helperText('Select the language for this translation')
                ->columnSpan(2),
            
            Forms\Components\TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255)
                ->columnSpan(2),
            
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->helperText('URL-friendly version of the title')
                ->columnSpan(2),
            
            Forms\Components\Textarea::make('tldr')
                ->label('TL;DR (Summary)')
                ->rows(3)
                ->maxLength(255)
                ->nullable()
                ->live()
                ->helperText(function ($state, Forms\Components\Textarea $component) {
                    $max = $component->getMaxLength();
                    $remaining = $max - strlen($state ?? '');
                    return "Brief summary of the post. Remaining characters: $remaining / $max.";
                })
                ->columnSpan(2),
            
            Forms\Components\MarkdownEditor::make('content')
                ->label('Content')
                ->required()
                ->toolbarButtons([
                    'attachFiles', 'blockquote', 'bold', 'bulletList',
                    'codeBlock', 'heading', 'italic', 'link',
                    'orderedList', 'redo', 'strike', 'table', 'undo',
                ])
                ->columnSpan(2),
            
            Forms\Components\TextInput::make('seo_title')
                ->label('SEO Title')
                ->maxLength(60)
                ->nullable()
                ->live()
                ->helperText(function ($state, Forms\Components\TextInput $component) {
                    $max = $component->getMaxLength();
                    $remaining = $max - strlen($state ?? '');
                    return "Optimal length: 50-60 characters. Remaining: $remaining / $max.";
                })
                ->columnSpan(2),
            
            Forms\Components\Textarea::make('seo_description')
                ->label('SEO Description')
                ->rows(2)
                ->maxLength(160)
                ->nullable()
                ->live()
                ->helperText(function ($state, Forms\Components\Textarea $component) {
                    $max = $component->getMaxLength();
                    $remaining = $max - strlen($state ?? '');
                    return "Optimal length: 150-160 characters. Remaining: $remaining / $max.";
                })
                ->columnSpan(2),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        $availableLocales = config('blogr.locales.available', ['en', 'fr', 'es', 'de']);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label('Language')
                    ->formatStateUsing(fn (string $state) => strtoupper($state))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        config('blogr.locales.default', 'en') => 'success',
                        default => 'info',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('Slug copied'),
                
                Tables\Columns\TextColumn::make('reading_time')
                    ->label('Reading Time')
                    ->formatStateUsing(fn (?int $state) => $state ? "{$state} min" : 'N/A')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('has_content')
                    ->label('Content')
                    ->boolean()
                    ->getStateUsing(fn (Model $record) => !empty($record->content))
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label('Language')
                    ->options(array_combine(
                        $availableLocales,
                        array_map('strtoupper', $availableLocales)
                    )),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Translation')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create Translation')
                    ->modalWidth('5xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['content'])) {
                            $wpm = config('blogr.reading_speed.words_per_minute', 200);
                            $wordCount = str_word_count(strip_tags($data['content']));
                            $data['reading_time'] = max(1, ceil($wordCount / $wpm));
                        }
                        return $data;
                    })
                    ->successNotificationTitle('Translation created successfully'),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading(fn (Model $record) => "Edit Translation (" . strtoupper($record->locale) . ")")
                    ->modalWidth('5xl')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['content'])) {
                            $wpm = config('blogr.reading_speed.words_per_minute', 200);
                            $wordCount = str_word_count(strip_tags($data['content']));
                            $data['reading_time'] = max(1, ceil($wordCount / $wpm));
                        }
                        return $data;
                    })
                    ->successNotificationTitle('Translation updated successfully'),
                
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->successNotificationTitle('Translation deleted successfully'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No translations yet')
            ->emptyStateDescription('Add translations in different languages to reach a wider audience')
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add First Translation')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('5xl'),
            ])
            ->defaultSort('locale');
    }

    protected function canCreate(): bool
    {
        $availableLocales = config('blogr.locales.available', ['en', 'fr', 'es', 'de']);
        $existingLocales = $this->getOwnerRecord()
            ->translations()
            ->pluck('locale')
            ->toArray();
        
        return count($availableLocales) > count($existingLocales);
    }
}
