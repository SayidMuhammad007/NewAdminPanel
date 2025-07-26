<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Xizmatlar';
    
    protected static ?string $modelLabel = 'Xizmat';
    
    protected static ?string $pluralModelLabel = 'Xizmatlar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Asosiy ma\'lumotlar')
                    ->schema([
                        Forms\Components\Tabs::make('Til versiyalari')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('O\'zbek')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_uz')
                                            ->label('Nomi (O\'zbek)')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Xizmat nomini kiriting'),
                                        Forms\Components\Textarea::make('description_uz')
                                            ->label('Tavsifi (O\'zbek)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Xizmat tavsifini kiriting'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Rus')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_ru')
                                            ->label('Nomi (Rus)')
                                            ->maxLength(255)
                                            ->placeholder('Введите название услуги'),
                                        Forms\Components\Textarea::make('description_ru')
                                            ->label('Tavsifi (Rus)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Введите описание услуги'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Ingliz')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_en')
                                            ->label('Nomi (Ingliz)')
                                            ->maxLength(255)
                                            ->placeholder('Enter service name'),
                                        Forms\Components\Textarea::make('description_en')
                                            ->label('Tavsifi (Ingliz)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Enter service description'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Turk')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_tr')
                                            ->label('Nomi (Turk)')
                                            ->maxLength(255)
                                            ->placeholder('Hizmet adını girin'),
                                        Forms\Components\Textarea::make('description_tr')
                                            ->label('Tavsifi (Turk)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Hizmet açıklamasını girin'),
                                    ]),
                            ])->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Rasmlar')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Xizmat rasmlari')
                            ->image()
                            ->multiple()
                            ->directory('services')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(8)
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Maksimal 8 ta rasm yuklash mumkin. Har bir fayl 5MB dan oshmasligi kerak.')
                            ->columnSpanFull()
                            ->reorderable()
                            ->openable()
                            ->downloadable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Rasm')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(),
                    
                Tables\Columns\TextColumn::make('name_uz')
                    ->label('Nomi (O\'zbek)')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                    
                Tables\Columns\TextColumn::make('name_ru')
                    ->label('Nomi (Rus)')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Nomi (Ingliz)')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('name_tr')
                    ->label('Nomi (Turk)')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('description_uz')
                    ->label('Tavsifi')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan vaqti')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Yangilangan vaqti')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->label('Yaratilgan vaqti bo\'yicha')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dan'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Gacha'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                    
                Tables\Filters\SelectFilter::make('has_images')
                    ->label('Rasm mavjudligi')
                    ->options([
                        '1' => 'Rasmi bor',
                        '0' => 'Rasmi yo\'q',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['value'])) {
                            return $query;
                        }
                        
                        return $data['value'] === '1'
                            ? $query->whereNotNull('images')->where('images', '!=', '[]')
                            : $query->where(function ($q) {
                                $q->whereNull('images')->orWhere('images', '[]');
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ko\'rish'),
                Tables\Actions\EditAction::make()
                    ->label('Tahrirlash'),
                Tables\Actions\DeleteAction::make()
                    ->label('O\'chirish'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Tanlanganlarni o\'chirish'),
                ])->label('Ommaviy amallar'),
            ])
            ->emptyStateHeading('Xizmatlar topilmadi')
            ->emptyStateDescription('Hozircha birorta xizmat qo\'shilmagan.')
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}