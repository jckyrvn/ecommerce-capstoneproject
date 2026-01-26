<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{
    Group,
    Section,
    Select,
    ToggleButtons,
    TextInput,
    Textarea,
    Repeater,
    Placeholder,
    Hidden
};
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Product;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->columnSpanFull()
                    ->schema([

                        /* ================= ORDER INFO ================= */
                        Section::make('Order Information')
                            ->columns(2)
                            ->schema([

                                Select::make('user_id')
                                    ->label('Customer')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('payment_method')
                                    ->options([
                                        'stripe' => 'Stripe',
                                        'cod' => 'Cash On Delivery',
                                    ])
                                    ->required(),

                                Select::make('payment_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                    ])
                                    ->default('pending')
                                    ->required(),

                                ToggleButtons::make('status')
                                    ->options([
                                        'new' => 'New',
                                        'processing' => 'Processing',
                                        'shipped' => 'Shipped',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->inline()
                                    ->default('new')
                                    ->required()
                                    ->colors([
                                        'new' => 'info',
                                        'processing' => 'warning',
                                        'shipped' => 'info',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger',
                                    ])
                                    ->icons([
                                        'new' => 'heroicon-m-sparkles',
                                        'processing' => 'heroicon-m-arrow-path',
                                        'shipped' => 'heroicon-m-truck',
                                        'delivered' => 'heroicon-m-check-badge',
                                        'cancelled' => 'heroicon-m-x-circle',
                                    ]),

                                Select::make('currency')
                                    ->options([
                                        'IDR' => 'IDR',
                                        'USD' => 'USD',
                                        'EUR' => 'EUR',
                                        'GBP' => 'GBP',
                                    ])
                                    ->default('IDR')
                                    ->required(),

                                Select::make('shipping_method')
                                    ->options([
                                        'FedEx' => 'FedEx',
                                        'UPS' => 'UPS',
                                        'DHL' => 'DHL',
                                        'USPS' => 'USPS',
                                    ]),

                                Textarea::make('notes')
                                    ->columnSpanFull(),
                            ]),

                        /* ================= ORDER ITEMS ================= */
                        Section::make('Order Items')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->columns(12)
                                    ->schema([

                                        Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->reactive()
                                            ->required()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                $price = Product::find($state)?->price ?? 0;
                                                $set('unit_amount', $price);
                                                $set('total_amount', $price);
                                            })
                                            ->columnSpan(4),

                                        TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->reactive()
                                            ->required()
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $set('total_amount', $state * ($get('unit_amount') ?? 0));
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('unit_amount')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->required()
                                            ->columnSpan(3),

                                        TextInput::make('total_amount')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated()
                                            ->required()
                                            ->columnSpan(3),
                                    ]),
                            ]),

                        /* ================= GRAND TOTAL ================= */
                        Placeholder::make('grand_total_display')
                            ->label('Grand Total')
                            ->content(function (Get $get, Set $set) {
                                $total = 0;

                                foreach ($get('items') ?? [] as $key => $item) {
                                    $total += $item['total_amount'] ?? 0;
                                }

                                $set('grand_total', $total);

                                return "Rp." . number_format($total, 2);
                            }),

                        Hidden::make('grand_total')
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grand_total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->searchable()
                    ->sortable(),

                SelectColumn::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->sortable(),
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
            AddressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
