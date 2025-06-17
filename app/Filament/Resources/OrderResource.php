<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Utama')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                    Forms\Components\Select::make('gender')
                                        ->options([
                                            'male' => 'Laki-Laki',
                                            'female' => 'Perempuan'
                                        ])
                                        ->required(),
                            ])
                            // ->columns(2),
                ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Tambahan')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('birthday'),
                            ])
                            // ->columns(2),
                ]),
                Forms\Components\Section::make('Produk Dipesan')
                    // ->columns(2)
                    ->schema([ 
                        self::getItemsRepeater(),
                    ]),
                
                
                
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Pembayaran')
                            ->schema([
                                Forms\Components\Select::make('payment_method_id')
                                    ->relationship('payment_method', 'name')
                                    ->required() // Add this to ensure payment method is selected
                                    ->live() // Add live() here too
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        // Add null check here
                                        if ($state) {
                                            $paymentMethod = PaymentMethod::find($state);
                                            if ($paymentMethod) {
                                                $set('is_cash', $paymentMethod->is_cash);
                                                if (!$paymentMethod->is_cash) {
                                                    $set('paid_amount', $get('total_price'));
                                                    $set('change_amount', 0);
                                                } else {
                                                    // Reset paid amount for cash payments
                                                    $set('paid_amount', null);
                                                    $set('change_amount', 0);
                                                }
                                            }
                                        } else {
                                            // Reset values when no payment method is selected
                                            $set('is_cash', false);
                                            $set('paid_amount', null);
                                            $set('change_amount', 0);
                                        }
                                    })
                                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        // Add null check here too
                                        if ($state) {
                                            $paymentMethod = PaymentMethod::find($state);
                                            if ($paymentMethod) {
                                                $set('is_cash', $paymentMethod->is_cash);
                                                if (!$paymentMethod->is_cash) {
                                                    $set('paid_amount', $get('total_price'));
                                                    $set('change_amount', 0);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\Hidden::make('is_cash')
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('paid_amount')
                                    ->numeric()
                                    ->label('Jumlah Dibayar')
                                    ->live()
                                    ->disabled(fn (Forms\Get $get) => !$get('is_cash'))
                                    ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                        self::updateExchangePaid($get, $set);
                                    }),

                                Forms\Components\TextInput::make('change_amount')
                                    ->numeric()
                                    ->readOnly()
                                    ->label('Kembalian'),
                    ])
                ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Total Harga dan Catatan')
                            ->schema([
                                Forms\Components\TextInput::make('total_price')
                                    ->required()
                                    ->readOnly()
                                    ->numeric(),
                                Forms\Components\Textarea::make('note')
                                    ->columnSpanFull(),
                    ])
                ]),
                
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender'),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater {
        return Repeater::make('order_products')
            ->relationship()
            ->live()
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set){
                self::updateTotalPrice($get, $set);
            })
            ->columns([
                'md' => 10
            ])
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produk')
                    ->options(Product::query()->where('stock', '>', 0)->pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->columnSpan([
                        'md' => 5
                    ])
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state){
                        // Add null check here
                        if ($state) {
                            $product = Product::find($state);
                            
                            if ($product) {
                                $set('unit_price', $product->price ?? 0);
                                $set('stock', $product->stock ?? 0);
                            }
                        }
                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // Add null check here
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('unit_price', $product->price ?? 0);
                                $set('stock', $product->stock ?? 0);
                                $quantity = $get('quantity') ?? 1;
                                $stock = $get('stock');
                                self::updateTotalPrice($get, $set);
                            }
                        } else {
                            // Reset values when no product is selected
                            $set('unit_price', 0);
                            $set('stock', 0);
                            self::updateTotalPrice($get, $set);
                        }
                    })
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->minValue(1)
                    ->columnSpan([
                        'md' => 1
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $stock = $get('stock');
                        if($state > $stock) {
                            $set('quantity', $stock);
                            Notification::make()
                                ->title('Jumlah melebihi stok yang tersedia')
                                ->danger()
                                ->send();
                        }
                        self::updateTotalPrice($get, $set);
                    }),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->columnSpan([
                        'md' => 1
                    ]),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->default(0)
                    ->readOnly()
                    ->columnSpan([
                        'md' => 3
                    ]),
                ]);
    }

    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void {
        $selectedProducts = collect($get('order_products'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
        
        if ($selectedProducts->isEmpty()) {
            $set('total_price', 0);
            return;
        }
        
        $price = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');
        $total = $selectedProducts->reduce(function($total, $product) use ($price) {
            return $total + ($price[$product['product_id']]  * $product['quantity']);
        }, 0);

        $set('total_price', $total);
    }

    protected static function updateExchangePaid(Forms\Get $get, Forms\Set $set): void {
        $paidAmount = (int) $get('paid_amount') ?? 0;
        $totalPrice = (int) $get('total_price') ?? 0;
        $exchangePaid = $paidAmount - $totalPrice;
        $set('change_amount', $exchangePaid);
    }
}