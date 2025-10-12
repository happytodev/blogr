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
        $data = parent::mutateFormDataBeforeFill($data);
        
        // Ensure bio is included in the form data
        $user = $this->getUser();
        $data['bio'] = $user->bio;
        
        // For avatar in Filament v4, we DON'T set it here
        // FileUpload will load it automatically from the model via getStateUsing
        // Setting it here can cause the "loading" issue
        
        return $data;
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = parent::mutateFormDataBeforeSave($data);
        
        // Ensure bio is saved
        if (!isset($data['bio'])) {
            $data['bio'] = $this->getUser()->bio;
        }
        
        // Avatar is automatically handled by FileUpload component
        // It will either be null (deleted), a string path (unchanged or new upload), or array (new upload)
        
        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Textarea::make('bio')
                    ->label('Biography')
                    ->maxLength(500)
                    ->rows(4)
                    ->nullable(),
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
            ]);
    }
}
