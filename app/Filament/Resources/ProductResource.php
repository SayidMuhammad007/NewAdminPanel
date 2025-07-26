<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    
    protected static ?string $navigationLabel = 'Mahsulotlar';
    
    protected static ?string $modelLabel = 'Mahsulot';
    
    protected static ?string $pluralModelLabel = 'Mahsulotlar';

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
                                            ->placeholder('Mahsulot nomini kiriting'),
                                        Forms\Components\Textarea::make('description_uz')
                                            ->label('Tavsifi (O\'zbek)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Mahsulot tavsifini kiriting'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Rus')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_ru')
                                            ->label('Nomi (Rus)')
                                            ->maxLength(255)
                                            ->placeholder('Введите название продукта'),
                                        Forms\Components\Textarea::make('description_ru')
                                            ->label('Tavsifi (Rus)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Введите описание продукта'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Ingliz')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_en')
                                            ->label('Nomi (Ingliz)')
                                            ->maxLength(255)
                                            ->placeholder('Enter product name'),
                                        Forms\Components\Textarea::make('description_en')
                                            ->label('Tavsifi (Ingliz)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Enter product description'),
                                    ]),
                                Forms\Components\Tabs\Tab::make('Turk')
                                    ->schema([
                                        Forms\Components\TextInput::make('name_tr')
                                            ->label('Nomi (Turk)')
                                            ->maxLength(255)
                                            ->placeholder('Ürün adını girin'),
                                        Forms\Components\Textarea::make('description_tr')
                                            ->label('Tavsifi (Turk)')
                                            ->rows(3)
                                            ->maxLength(1000)
                                            ->placeholder('Ürün açıklamasını girin'),
                                    ]),
                            ])->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Rasmlar')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Mahsulot rasmlari')
                            ->image()
                            ->multiple()
                            ->directory('products')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(10)
                            ->maxSize(5120) // 5MB
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Maksimal 10 ta rasm yuklash mumkin. Har bir fayl 5MB dan oshmasligi kerak.')
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
            ->emptyStateHeading('Mahsulotlar topilmadi')
            ->emptyStateDescription('Hozircha birorta mahsulot qo\'shilmagan.')
            ->emptyStateIcon('heroicon-o-cube')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}