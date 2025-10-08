<?php

namespace Happytodev\Blogr\Filament\Resources\Categories\RelationManagers;

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
            
            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255)
                ->columnSpan(2),
            
            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->helperText('URL-friendly version of the name')
                ->columnSpan(2),
            
            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3)
                ->maxLength(500)
                ->nullable()
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
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('Slug copied'),
                
                Tables\Columns\IconColumn::make('has_description')
                    ->label('Description')
                    ->boolean()
                    ->getStateUsing(fn (Model $record) => !empty($record->description))
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
                    ->modalWidth('3xl')
                    ->successNotificationTitle('Translation created successfully'),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading(fn (Model $record) => "Edit Translation (" . strtoupper($record->locale) . ")")
                    ->modalWidth('3xl')
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
            ->emptyStateDescription('Add translations in different languages')
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Add First Translation')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('3xl'),
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
