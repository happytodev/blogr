<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->hiddenOn('edit'),
                Select::make('role')
                    ->label('Role')
                    ->options(Role::all()->pluck('name', 'name'))
                    ->required()
                    ->afterStateHydrated(function (Select $component, $state, $record) {
                        if ($record && $record->roles->isNotEmpty()) {
                            $component->state($record->roles->first()->name);
                        }
                    })
                    ->dehydrated(false),
            ]);
    }
}
