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
        // Ensure bio and avatar are included in the form data
        $user = $this->getUser();
        $data['bio'] = $user->bio;
        $data['avatar'] = $user->avatar;
        
        return parent::mutateFormDataBeforeFill($data);
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
                    ->disk('public')
                    ->directory('avatars')
                    ->nullable(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
