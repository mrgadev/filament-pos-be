<div class="grid grid-cols-1 dark:bg-gray-900 md:grid-cols-3 gap-4">
    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <div class="mb-4 flex gap-3">
            <input type="text" placeholder="Cari produk" class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" wire:model.live.debounce.300ms="search">
            <x-filament::button wire:click="toggleScanner" color="primary">
                Scan Barcode
            </x-filament::button>
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

    <!-- QR Scanner Modal -->
    @if($showScanner)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" 
         x-data="{ show: true }" 
         x-show="show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-lg w-full mx-4 relative">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold dark:text-white">QR Code Scanner</h2>
                <x-filament::button wire:click="toggleScanner" color="gray" size="sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </x-filament::button>
            </div>
            
            <!-- Scanner Container -->
            <div id="reader" class="w-full min-h-[300px] bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Menginisialisasi kamera...</p>
                </div>
            </div>
            
            <!-- Instructions -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Arahkan kamera ke barcode/QR code untuk melakukan scan
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let scanner = null;
            let isScanning = false;
            
            window.addEventListener('scanner-toggled', function(event) {
                const shouldShow = event.detail[0].show;
                
                if (shouldShow && !isScanning) {
                    // Start scanner
                    setTimeout(() => {
                        const readerElement = document.getElementById('reader');
                        if (readerElement && !isScanning) {
                            isScanning = true;
                            
                            // Clear any existing content
                            readerElement.innerHTML = '';
                            
                            try {
                                scanner = new Html5QrcodeScanner(
                                    'reader',
                                    { 
                                        fps: 10,
                                        qrbox: { width: 250, height: 250 },
                                        aspectRatio: 1.0,
                                        showTorchButtonIfSupported: true,
                                        showZoomSliderIfSupported: true,
                                        defaultZoomValueIfSupported: 2,
                                        experimentalFeatures: {
                                            useBarCodeDetectorIfSupported: true
                                        }
                                    },
                                    false
                                );
                                
                                scanner.render(
                                    (decodedText, decodedResult) => {
                                        console.log('QR Code detected:', decodedText);
                                        // Send scan result to Livewire
                                        @this.call('handleScanResult', decodedText);
                                        stopScanner();
                                    },
                                    (error) => {
                                        // Handle scan errors (but don't log frame-level errors)
                                        if (error && !error.includes('NotFoundException')) {
                                            console.warn('QR Scanner error:', error);
                                        }
                                    }
                                );
                                
                                console.log('Scanner initialized successfully');
                            } catch (error) {
                                console.error('Failed to initialize scanner:', error);
                                isScanning = false;
                                
                                // Show fallback message
                                readerElement.innerHTML = `
                                    <div class="text-center p-4">
                                        <p class="text-red-600 dark:text-red-400 mb-2">Tidak dapat mengakses kamera</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Pastikan browser memiliki izin akses kamera</p>
                                        <button onclick="requestCameraPermission()" class="mt-2 px-4 py-2 bg-blue-500 text-white rounded">
                                            Coba Lagi
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    }, 300); // Increased timeout
                } else if (!shouldShow) {
                    stopScanner();
                }
            });
            
            function stopScanner() {
                if (scanner && isScanning) {
                    scanner.clear().then(() => {
                        scanner = null;
                        isScanning = false;
                        console.log('Scanner stopped successfully');
                    }).catch(error => {
                        console.error('Error clearing scanner:', error);
                        scanner = null;
                        isScanning = false;
                    });
                }
            }
            
            // Global function to request camera permission
            window.requestCameraPermission = function() {
                navigator.MediaDevices?.getUserMedia({ video: true })
                    .then(stream => {
                        // Stop the stream immediately, we just wanted to get permission
                        stream.getTracks().forEach(track => track.stop());
                        // Trigger scanner restart
                        @this.call('toggleScanner');
                        setTimeout(() => @this.call('toggleScanner'), 100);
                    })
                    .catch(err => {
                        alert('Akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.');
                        console.error('Camera permission denied:', err);
                    });
            };
        });
    </script>
    @endif
</div>

<script src="https://unpkg.com/html5-qrcode"></script>