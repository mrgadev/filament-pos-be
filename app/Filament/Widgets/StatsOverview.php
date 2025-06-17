<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $product_count = Product::count();
        $order_count = Order::count();
        $omzet = Order::sum('total_price');
        $expense = Expense::sum('amount');
        return [
            Stat::make('Jumlah produk', $product_count),
            Stat::make('Order', $order_count),
            Stat::make('Omzet', 'Rp. '.number_format($omzet, 0, ',', '.'))
                ->description('Total omzet dari semua order')
                ->color('success'),
            Stat::make('Pengeluaran', 'Rp. '.number_format($expense, 0, ',', '.'))
        ];
    }
}
