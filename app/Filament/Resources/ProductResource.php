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
use Filament\Forms\Components\{
    TextInput,
    Section,
    Group,
    FileUpload,
    MarkdownEditor,
    Select,
    Toggle
};
use Filament\Tables\Columns\{
    TextColumn,
    IconColumn
};
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\{
    ActionGroup,
    ViewAction,
    EditAction,
    DeleteAction
};
use Illuminate\Support\Str;
class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make('Product Information')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }
                                        $set('slug', Str::slug($state));
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
                            ])
                            ->columns(2),

                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products'),

                        Section::make('Images')
                            ->schema([
                                FileUpload::make('images')
                                    ->multiple()
                                    ->directory('products')
                                    ->maxFiles(5)
                                    ->reorderable(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Price')
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('IDR'),
                            ]),

                        Section::make('Associations')
                            ->schema([
                                Select::make('category_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship('category', 'name'),

                                Select::make('brand_id')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->relationship('brand', 'name'),
                            ]),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('in_stock')
                                    ->required()
                                    ->default(true),

                                Toggle::make('is_active')
                                    ->required()
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->required(),

                                Toggle::make('on_sale')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                TextColumn::make('category.name')
                    ->sortable(),

                TextColumn::make('brand.name')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                IconColumn::make('is_featured')->boolean(),
                IconColumn::make('on_sale')->boolean(),
                IconColumn::make('in_stock')->boolean(),
                IconColumn::make('is_active')->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),

                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
}
