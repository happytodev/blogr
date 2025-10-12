<?php

namespace Happytodev\Blogr\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\MessageBag;

class EditProfile extends BaseEditProfile
{
    public function getErrorBag()
    {
        $bag = parent::getErrorBag();
        return $bag instanceof MessageBag ? $bag : new MessageBag();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure avatar is treated as a string, not parsed as datetime
        // This prevents Carbon from trying to parse file paths
        if (isset($data['avatar'])) {
            $data['avatar'] = (string) $data['avatar'];
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);
        
        // Bio is automatically handled by Filament with the 'array' cast
        // Avatar is automatically handled by FileUpload component
        
        return $data;
    }

    public function form(Schema $schema): Schema
    {
        // Get enabled locales from config
        $localesEnabled = config('blogr.locales.enabled', false);
        $availableLocales = config('blogr.locales.available', ['en' => 'English']);
        
        $bioComponents = [];
        
        if ($localesEnabled && count($availableLocales) > 1) {
            // Multiple locales - create a textarea for each language
            foreach ($availableLocales as $locale => $label) {
                $bioComponents[] = Textarea::make("bio.{$locale}")
                    ->label("Biography - {$label}")
                    ->maxLength(500)
                    ->rows(4)
                    ->nullable()
                    ->helperText("Your biography in {$label}. Displayed on your author profile page.");
            }
        } else {
            // Single locale - just use 'en' as default
            $bioComponents[] = Textarea::make('bio.en')
                ->label('Biography')
                ->maxLength(500)
                ->rows(4)
                ->nullable()
                ->helperText('Your biography. Displayed on your author profile page.');
        }
        
        return $schema
            ->components(array_merge([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
            ], 
            $bioComponents,
            [
                FileUpload::make('avatar')
                    ->label('Avatar')
                    ->image()
                    ->avatar()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                    ->maxSize(2048)
                    ->imageEditor()
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('200')
                    ->imageResizeTargetHeight('200')
                    ->preserveFilenames()
                    ->fetchFileInformation(false)
                    ->nullable(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]));
    }
}
