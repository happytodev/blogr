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
        
        // Ensure bio is an array for the form fields (bio.en, bio.fr, etc.)
        // Eloquent should already decode it via the 'array' cast, but we double-check
        if (isset($data['bio'])) {
            if (is_string($data['bio'])) {
                // If it's a string, try to decode it as JSON
                $decoded = json_decode($data['bio'], true);
                $data['bio'] = is_array($decoded) ? $decoded : ['en' => $data['bio']];
            } elseif (!is_array($data['bio'])) {
                // If it's neither string nor array, create default structure
                $data['bio'] = ['en' => ''];
            }
        } else {
            // If bio is not set, create empty structure for all locales
            $localesEnabled = config('blogr.locales.enabled', false);
            $availableLocales = config('blogr.locales.available', ['en' => 'English']);
            
            $data['bio'] = [];
            if ($localesEnabled && count($availableLocales) > 1) {
                foreach (array_keys($availableLocales) as $locale) {
                    $data['bio'][$locale] = '';
                }
            } else {
                $data['bio']['en'] = '';
            }
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
        
        // Normalize locales array - handle both ['en', 'fr'] and ['en' => 'English', 'fr' => 'Français']
        $normalizedLocales = [];
        foreach ($availableLocales as $key => $value) {
            if (is_numeric($key)) {
                // Array is ['en', 'fr', 'de'] format
                $normalizedLocales[$value] = ucfirst($value);
            } else {
                // Array is ['en' => 'English'] format
                $normalizedLocales[$key] = $value;
            }
        }
        
        $bioComponents = [];
        
        if ($localesEnabled && count($normalizedLocales) > 1) {
            // Multiple locales - create a textarea for each language
            foreach ($normalizedLocales as $locale => $label) {
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
