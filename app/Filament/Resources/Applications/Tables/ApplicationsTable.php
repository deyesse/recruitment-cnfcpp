<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Filament\Exports\ApplicationExporter;
use Carbon\Carbon;
use App\Models\Position;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(ApplicationExporter::class),
            ])
            ->columns([
                TextColumn::make('id')
                    ->label('رقم التسجيل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(
                        query: fn ($query, string $search): \Illuminate\Database\Eloquent\Builder => $query->where('applications.name', 'like', "%{$search}%")
                    ),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),

                TextColumn::make('tel')
                    ->label('رقم الهاتف')
                    ->searchable(),

                TextColumn::make('age')
                    ->label("العمر\n(سنة)")
                    ->state(fn ($record) => $record->birth_date ? $record->birth_date->age : '-')
                    ->alignCenter()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('birth_date', $direction === 'asc' ? 'desc' : 'asc');
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ ووقت التسجيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('position')
                    ->label('رمز الوظيفة / الخطة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('calculated_score')
                    ->label('مجموع النقاط')
                    ->state(fn ($record) => $record->calculated_score ?? $record->calculateScore())
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('calculated_score', $direction);
                    })
                    ->numeric(decimalPlaces: 3, locale: 'en_US'),

                TextColumn::make('test_grade')
                    ->placeholder('لم يجتز الاختبار')
                    ->label('نتيجة الاختبار')
                    ->sortable(),
                TextColumn::make('final')
                    ->state(fn ($record) => $record->test_grade ? (($record->calculated_score ?? $record->calculateScore()) + $record->test_grade) / 2 : null)
                    ->placeholder('لم يجتز الاختبار')
                    ->label('النتيجة النهائية')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('الخطة الوظيفية')
                    ->options(
                        Position::orderBy('code')
                            ->get()
                            ->mapWithKeys(fn ($p) => [$p->code => $p->code . ' – ' . $p->name])
                    )
                    ->searchable()
                    ->placeholder('الكل'),
            ])
            ->groups([
                Group::make('position')->label('الوظيفة'),
            ])
            ->recordActions([
                Action::make('passer')
                    ->label('قبول اولي')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->status = 'مقبول';

                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === 'جديد'),
                Action::make('revert')
                    ->label('إلغاء القبول')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->action(function ($record) {
                        $record->test_grade = null;
                        $record->status = 'جديد';
                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === 'مقبول'),

                Action::make('note_test')
                    ->label('تقييم الاختبار')
                    ->schema([
                        TextInput::make('test_grade')
                            ->label('درجة الاختبار')
                            ->numeric()
                            ->maxValue(20)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->test_grade = $data['test_grade'];
                        $record->save();
                    })
                    ->visible(fn ($record) => $record->status === 'مقبول'),

                ViewAction::make()
                    ->label('عرض'),
            ])
            ->toolbarActions([
            ]);
    }
}
