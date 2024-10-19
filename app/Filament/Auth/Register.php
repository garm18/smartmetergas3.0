<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Component;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Support\Htmlable;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('username') // menambahkan kolom username pada laman login
                    ->required()->unique()
                    ->rules('regex:/^[a-zA-Z0-9_]+$/'),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Hidden::make('roles')->default('customer'),
            ])
            ->statePath('data');
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'), // agar pesan error muncul dihalamn register pada salah input
        ]);
    }
}