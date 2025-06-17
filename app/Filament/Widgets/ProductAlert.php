<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductAlert extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Stok Hampir Habis';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('stock', '<=', 10) // Adjust the condition as needed
                    ->orderBy('stock', 'asc') // Optional: order by stock level
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('slug')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->color(static function ($state) {
                        if($state < 5) {
                            return 'danger';
                        } elseif ($state < 10) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    })
                    ->sortable(),
            ]);
    }
}
