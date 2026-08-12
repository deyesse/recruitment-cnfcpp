<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),

                Select::make('role')
                    ->label('الدور / الصلاحية')
                    ->options(function ($record) {
                        $authUser = auth()->user();
                        if ($authUser?->isSuperAdmin()) {
                            return [
                                'super_admin' => 'مدير فائق (Super Admin)',
                                'admin' => 'مدير (Admin)',
                                'gestionnaire' => 'متصرف (Gestionnaire)',
                            ];
                        }

                        if ($record && $record->id === $authUser?->id) {
                            return [
                                'admin' => 'مدير (Admin)',
                            ];
                        }

                        return [
                            'gestionnaire' => 'متصرف (Gestionnaire)',
                        ];
                    })
                    ->default('gestionnaire')
                    ->disabled(fn ($record) => $record && $record->id === auth()->id() && ! auth()->user()?->isSuperAdmin())
                    ->required(),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required(),

                DateTimePicker::make('email_verified_at')
                    ->label('تاريخ التحقق من البريد الإلكتروني'),

                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state)),
            ]);
    }
}
