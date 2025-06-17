<div class="grid grid-cols-1 dark:bg-gray-900 md:grid-cols-3 gap-4">
    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <div class="mb-4">
            <input type="text" placeholder="Cari produk" class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" wire:model.live.debounce.300ms="search">
        </div>
        <div class="flex-grow">
            <div class="grid grid-cols-8 sm:grid-cols-3 md:grid-cols-8 gap-4">
                @foreach ($products as $product)    
                    <div wire:click.prevent="addToOrder('{{ $product->id }}')" class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow cursor-pointer">
                        <img src="{{$product->image_url}}" alt="" class="w-full h-16 object-cover rounded-lg mb-2">
                        <h3 class="text-sm font-semibold">{{$product->name}}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">{{$product->price_formatted}}</p>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Stok: {{$product->stock}}</p>
                    </div>
                @endforeach
            </div>
            <div class="py-4">
                {{$products->links()}}
            </div>
        </div>
    </div>

    <div class="md:col-span-1 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        @if (count($orderItems) > 0)    
        <div class="py-4">
            <h3 class="text-lg font-semibold text-center">Total: Rp. {{number_format($this->calculateTotal(), 0, ',', '.')}}</h3>
        </div>
        @endif
        <div class="mb-4">
            @foreach ($orderItems as $item)
                <div class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow mb-2">
                <div class="flex items-center">
                        <img src="{{$item['image_url']}}" alt="" class="w-16 h-16 object-cover rounded-lg mb-2">
                        <div class="px-2">
                            <h3 class="text-sm font-semibold">{{$item['name']}}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-xs">Rp. {{$item['price']}}</p>
                        </div>
                </div>
                <div class="flex items-center">
                        <x-filament::button color="warning" wire:click="decreaseQuantity('{{$item['product_id']}}')">-</x-filament::button>
                        <span class="px-4">{{$item['quantity']}}</span>
                        <x-filament::button color="success" wire:click="increaseQuantity('{{$item['product_id']}}')">+</x-filament::button>
                </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="checkout">
            {{$this->form}}
            <x-filament::button type="submit" class="w-full mt-3" color="primary">Bayar</x-filament::button>
        </form>

        <div class="mt-2">

        </div>
    </div>
</div>