<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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
                Toggle::make('is_admin')
                    ->label('حساب مدير / مشرف')
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
                    ->required(),
                Textarea::make('two_factor_secret')
                    ->label('رمز المصادقة الثنائية')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->label('رموز استعادة المصادقة الثنائية')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at')
                    ->label('تاريخ تأكيد المصادقة الثنائية'),
            ]);
    }
}
