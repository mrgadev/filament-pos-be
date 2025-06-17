<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Set;

class Pos extends Component implements HasForms
{
    use InteractsWithForms;
    use WithPagination;
    protected $paginationTheme = 'tailwind';
    public $search  = '';
    public $name_customer = '';
    public $paymentMethod;
    public $gender = 'male';
    public $payment_method_id;
    public $orderItems = [];
    public $total_price = 0;

    public function render()
    {
        return view('livewire.pos', [
            'products' => Product::where('stock', '>', 0)
                ->search($this->search)
                ->orderBy('name')
                ->paginate(3),
        ]);
    }

    public function form(Form $form): Form {
        return $form
                ->schema([
                    Forms\Components\Section::make('Form Checkout')
                        ->schema([
                            Forms\Components\TextInput::make('name_customer')
                                ->required()
                                ->maxLength(255)
                                ->default(fn() => $this->nameCustomer),
                            Forms\Components\Select::make('gender')
                                ->options([
                                    'male' => 'Laki-Laki',
                                    'female' => 'Perempuan',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('total_price')
                                ->readOnly()
                                ->numeric()
                                ->default(fn() => $this->calculateTotal()),
                            Forms\Components\Select::make('payment_method_id')
                                ->label('Metode Pembayaran')
                                ->options($this->paymentMethod->pluck('name', 'id'))
                                ->required(),
                        ])
                ]);
    }

    public function mount() {
        if(session()->has('orderItems'))  {
            $this->orderItems = session()->get('orderItems', []);
        } else {
            $this->orderItems = [];
        }
        $this->paymentMethod = \App\Models\PaymentMethod::all();
        // $this->form->fill(['payment_methods', $this->paymentMethod]); 
        $this->form->fill(['payment_method_id' => $this->paymentMethod->first()?->id]);
    }

    public function addToOrder($productId) {
        $product = Product::find($productId);
        if($product) {
            if($product->stock <= 0) {
                Notification::make()
                    ->title('Stok tidak cukup')
                    ->body('Produk ini sudah habis stoknya.')
                    ->danger()
                    ->send();
                return;
            }

            $existingItemKey = null;
            foreach($this->orderItems as $key => $item) {
                if($item['product_id'] === $productId) {
                    $existingItemKey = $key;
                    break;
                }
            }

            if($existingItemKey != null) {
                $this->orderItems[$existingItemKey]['quantity']++;
            } else {
                $this->orderItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image_url' => $product->image_url,
                    'quantity' => 1,
                ];
            }

            session()->put('orderItems', $this->orderItems);
            // $this->calculateTotal();
            $this->calculateTotal();
            $this->form->fill(['total_price' => $this->total_price]);
            Notification::make()
                ->title('Produk ditambahkan')
                ->body("{$product->name} telah ditambahkan ke keranjang.")
                ->success()
                ->send();
        }
    }

    public function loadOrderItems($orderItems) {
        $this->orderItems = $orderItems ?? session()->get('orderItems', []);
        $this->calculateTotal();
        $this->form->fill(['total_price' => $this->total_price]);
        session()->put('orderItems', $this->orderItems);
    }

    public function increaseQuantity($productId) {
        $product = Product::find($productId);
        if(!$product) {
            Notification::make()
                ->title('Produk tidak ditemukan')
                ->body('Produk yang ingin ditambahkan tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        foreach($this->orderItems as $key => $item) {
            if($item['product_id'] == $productId) {
                if($item['quantity'] + 1 <= $product->stock) {
                    $this->orderItems[$key]['quantity']++;
                } else {
                    Notification::make()
                        ->title('Stok tidak cukup')
                        ->body('Tidak dapat menambah jumlah produk, stok tidak mencukupi.')
                        ->danger()
                        ->send();
                    return;
                }
            }
        }
        session()->put('orderItems', $this->orderItems);
        $this->calculateTotal();
        $this->form->fill(['total_price' => $this->total_price]);
    }

    public function decreaseQuantity($productId) {
        foreach($this->orderItems as $key => $item) {
            if($item['product_id'] == $productId) {
                if($this->orderItems[$key]['quantity'] > 1) {
                    $this->orderItems[$key]['quantity']--;
                } else {
                    unset($this->orderItems[$key]);
                    $this->orderItems = array_values($this->orderItems); // Reindex the array
                }
                break;
            }
        }
        session()->put('orderItems', $this->orderItems);
        $this->calculateTotal();
        $this->form->fill(['total_price' => $this->total_price]);
    }

    public function calculateTotal() {
        $total = 0;
        foreach($this->orderItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $this->total_price = $total;
        return $total;
    }

    public function checkout() {
        $this->validate([
            'name_customer' => 'required|max:255',
            'gender' => 'required|in:male,female',
            'payment_method_id' => 'required|exists:payment_methods,id',

        ]);



        $payment_method_id_temp = $this->payment_method_id;
        $order = Order::create([
            'name' => $this->name_customer,
            'gender' => $this->gender,
            'total_price' => $this->calculateTotal(),
            'payment_method_id' => $payment_method_id_temp,
        ]);

        foreach($this->orderItems as $item) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
            ]);
        }

        $this->orderItems = [];
        session()->forget('orderItems');
        return redirect()->to('admin/orders');
    }

}
