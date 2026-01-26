<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\OrderResource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;


class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderResource::getEloquentQuery()
                    ->latest()
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('id')->label('Order ID'),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('grand_total')
                    ->money('IDR'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('payment_method'),

                TextColumn::make('payment_status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Action::make('viewOrder')
                    ->label('View Order')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn($record) =>
                        OrderResource::getUrl('view', ['record' => $record])
                    ),
            ]);
    }
}
