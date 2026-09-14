<x-layouts.app title="Layar Kasir (POS)" header="Point of Sale (POS)">
    <div x-data="posTerminal({
            initialProducts: {{ Js::from($initialProducts) }},
            paymentMethods: {{ Js::from($paymentMethods) }},
            heldCarts: {{ Js::from($heldCarts) }},
            checkoutUrl: '{{ route('pos.checkout') }}',
            searchUrl: '{{ route('pos.search') }}',
            holdUrl: '{{ route('pos.hold') }}',
            recallUrl: '{{ url('/pos/recall') }}',
            csrfToken: '{{ csrf_token() }}'
         })"
         @keydown.window.f1.prevent="$refs.searchInput.focus()"
         @keydown.window.f4.prevent="openHoldModal()"
         @keydown.window.f8.prevent="quickPayExact()"
         @keydown.window.f9.prevent="openPaymentModal()"
         @keydown.window.escape="closeModals()"
         class="h-[calc(100vh-8.5rem)] flex flex-col -m-6 lg:-m-8">

        <!-- Top POS Status & Shortcut Bar -->
        <div class="bg-white border-b border-slate-200/80 px-6 py-2.5 flex items-center justify-between gap-4 shrink-0 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-slate-800">Register:</span>
                    <span class="font-mono text-xs font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                        {{ $activeShift->shift_number }}
                    </span>
                </div>
                <span class="text-slate-300">|</span>
                <div class="text-xs text-slate-600">
                    Kasir: <strong class="text-slate-900">{{ $activeShift->cashier->name }}</strong>
                </div>
                <span class="text-slate-300">|</span>
                <div class="text-xs text-slate-600">
                    Toko: <strong class="text-slate-900">{{ $store->name }}</strong>
                </div>
            </div>

            <!-- Quick Keyboard Shortcuts Pill -->
            <div class="hidden xl:flex items-center gap-2 text-[11px] text-slate-500 font-medium">
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono font-bold text-slate-700">F1</span> Cari / Scan
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono font-bold text-slate-700">F4</span> Tahan (Hold)
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono font-bold text-slate-700">F8</span> Uang Pas
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono font-bold text-slate-700">F9</span> Bayar Lengkap
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-slate-100 border border-slate-200 rounded font-mono font-bold text-slate-700">ESC</span> Tutup
            </div>

            <div class="flex items-center gap-2">
                <button type="button" 
                        @click="openHoldModal()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold transition-colors">
                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Antrean Ditahan</span>
                    <span x-show="heldList.length > 0" 
                          x-text="heldList.length" 
                          class="px-1.5 py-0.2 rounded-full bg-amber-500 text-white text-[10px] font-black">
                    </span>
                </button>

                <a href="{{ route('shifts.show', $activeShift) }}" 
                   class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold transition-colors">
                    Ringkasan Shift
                </a>
            </div>
        </div>

        <!-- POS Main Work Area (Left: Catalog, Right: Cart) -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- LEFT PANEL: Product Search & Catalog (60%) -->
            <div class="w-7/12 flex flex-col border-r border-slate-200/80 bg-slate-50/50">
                
                <!-- Search and Categories Filter Bar -->
                <div class="p-4 bg-white border-b border-slate-200/80 space-y-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text" 
                               x-ref="searchInput"
                               x-model="searchQuery" 
                               @keydown.enter.prevent="handleBarcodeScan()"
                               placeholder="Scan barcode scanner atau ketik nama produk / SKU... (Tekan F1)" 
                               class="w-full pl-11 pr-24 py-2.5 rounded-xl border-slate-300 text-sm font-medium focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                        <div class="absolute inset-y-0 right-2 flex items-center">
                            <span class="text-[10px] font-bold font-mono px-2 py-1 bg-slate-100 border border-slate-200 text-slate-500 rounded">
                                [Enter] Tambah
                            </span>
                        </div>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                        <button type="button" 
                                @click="filterCategory(null)"
                                :class="selectedCategoryId === null ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors">
                            Semua Produk
                        </button>
                        @foreach($categories as $category)
                            <button type="button" 
                                    @click="filterCategory({{ $category->id }})"
                                    :class="selectedCategoryId === {{ $category->id }} ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                                    class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div @click="addProductToCart(product)"
                                 class="bg-white rounded-2xl p-3.5 border border-slate-200/80 hover:border-indigo-400 hover:shadow-md transition-all cursor-pointer flex flex-col justify-between group relative select-none">
                                
                                <div>
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <span class="text-[10px] font-mono text-slate-400 truncate" x-text="product.sku || '-'"></span>
                                        <!-- Stock Badge -->
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold"
                                              :class="product.current_stock > 10 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (product.current_stock > 0 ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200')">
                                            Stok: <span x-text="product.current_stock"></span>
                                        </span>
                                    </div>

                                    <h4 class="font-bold text-xs text-slate-900 line-clamp-2 group-hover:text-indigo-600 transition-colors" x-text="product.name"></h4>
                                </div>

                                <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-slate-400" x-text="'/' + product.base_unit"></span>
                                        <div class="font-black text-sm text-slate-900 tabular-nums" x-text="'Rp ' + Number(product.selling_price).toLocaleString('id-ID')"></div>
                                    </div>
                                    <button type="button" class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-show="filteredProducts.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-400">
                        <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <p class="font-bold text-sm text-slate-600">Produk tidak ditemukan</p>
                        <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau pilih kategori Semua Produk.</p>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: Current Cart & Checkout (40%) -->
            <div class="w-5/12 flex flex-col bg-white">
                
                <!-- Cart Header -->
                <div class="p-3.5 border-b border-slate-200/80 flex items-center justify-between bg-slate-50/70">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <h3 class="font-bold text-sm text-slate-900">Keranjang Transaksi</h3>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-black" x-text="cartTotalQty + ' item'"></span>
                    </div>

                    <button type="button" 
                            x-show="cart.length > 0"
                            @click="clearCart()" 
                            class="text-xs text-rose-600 hover:text-rose-800 font-semibold transition-colors">
                        Kosongkan
                    </button>
                </div>

                <!-- Cart Items List -->
                <div class="flex-1 p-3 overflow-y-auto space-y-2.5">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-colors space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h5 class="font-bold text-xs text-slate-900 truncate" x-text="item.product_name"></h5>
                                    <span class="text-[10px] font-mono text-slate-400" x-text="item.sku || '-'"></span>
                                </div>
                                <button type="button" @click="removeItem(index)" class="text-slate-400 hover:text-rose-600 transition-colors p-0.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-200/60">
                                
                                <!-- Unit Selector (If multi-unit exists) -->
                                <div class="w-32">
                                    <template x-if="item.available_units.length > 0">
                                        <select x-model="item.product_unit_id" 
                                                @change="onUnitChanged(index)"
                                                class="w-full text-xs font-semibold rounded-lg border-slate-300 py-1 pl-2 pr-6 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                            <option :value="''" x-text="item.base_unit + ' (1)'"></option>
                                            <template x-for="u in item.available_units" :key="u.id">
                                                <option :value="u.id" x-text="u.unit_name + ' (' + u.conversion_factor + ')'"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="item.available_units.length === 0">
                                        <span class="text-xs font-bold text-slate-600" x-text="item.unit_name"></span>
                                    </template>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center border border-slate-300 rounded-lg bg-white overflow-hidden shadow-2xs">
                                    <button type="button" 
                                            @click="updateQty(index, -1)" 
                                            class="px-2 py-1 hover:bg-slate-100 text-slate-600 font-bold transition-colors">
                                        -
                                    </button>
                                    <input type="number" 
                                           x-model.number="item.quantity" 
                                           @input="calculateLineSubtotal(index)"
                                           min="1" 
                                           class="w-12 text-center text-xs font-bold border-0 p-0 focus:ring-0 tabular-nums">
                                    <button type="button" 
                                            @click="updateQty(index, 1)" 
                                            class="px-2 py-1 hover:bg-slate-100 text-slate-600 font-bold transition-colors">
                                        +
                                    </button>
                                </div>

                                <!-- Line Subtotal -->
                                <div class="text-right">
                                    <div class="font-extrabold text-xs text-slate-900 tabular-nums" x-text="'Rp ' + Number(item.subtotal).toLocaleString('id-ID')"></div>
                                    <span class="text-[10px] text-slate-400 tabular-nums" x-text="'@ Rp ' + Number(item.unit_price).toLocaleString('id-ID')"></span>
                                </div>

                            </div>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-400">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                        </div>
                        <p class="font-bold text-xs text-slate-600">Keranjang masih kosong</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pilih produk di katalog atau scan barcode.</p>
                    </div>
                </div>

                <!-- Cart Calculations & Action Buttons -->
                <div class="p-4 border-t border-slate-200/80 bg-slate-50 space-y-3 shrink-0">
                    
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Belanja:</span>
                            <span class="font-bold tabular-nums" x-text="'Rp ' + Number(cartSubtotal).toLocaleString('id-ID')"></span>
                        </div>

                        <!-- Transaction Discount -->
                        <div class="flex items-center justify-between gap-2 text-slate-600">
                            <span>Diskon Transaksi:</span>
                            <div class="flex items-center gap-1">
                                <span class="text-slate-400">Rp</span>
                                <input type="number" 
                                       x-model.number="transactionDiscount" 
                                       min="0" 
                                       step="500"
                                       class="w-24 text-right text-xs font-bold rounded-lg border-slate-300 py-1 px-2 focus:border-indigo-500 focus:ring-indigo-500 tabular-nums">
                            </div>
                        </div>

                        <!-- Supervisor Discount Warning -->
                        <div x-show="isDiscountOverThreshold" class="text-[10px] text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-1 rounded-lg flex items-center gap-1.5 font-bold">
                            <svg class="w-3.5 h-3.5 shrink-0 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285zM12 17.25h.008v.008H12v-.008z" />
                            </svg>
                            <span>Diskon &gt; 5% / Rp 20rb: Perlu PIN Supervisor</span>
                        </div>

                        <!-- Grand Total Display -->
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                            <span class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Total Tagihan:</span>
                            <span class="font-black text-2xl text-emerald-600 tabular-nums" x-text="'Rp ' + Number(cartGrandTotal).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <!-- Hold Cart -->
                        <button type="button" 
                                @click="openHoldModal()" 
                                :disabled="cart.length === 0"
                                class="py-2.5 px-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 disabled:opacity-50 text-slate-700 font-bold text-xs transition-colors flex items-center justify-center gap-1 shadow-2xs">
                            <span class="font-mono text-[10px] bg-slate-100 px-1 py-0.5 rounded border border-slate-200 text-slate-500">F4</span>
                            Tahan
                        </button>

                        <!-- Quick Cash Exact -->
                        <button type="button" 
                                @click="quickPayExact()" 
                                :disabled="cart.length === 0 || isCheckingOut"
                                class="py-2.5 px-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white font-bold text-xs transition-colors flex items-center justify-center gap-1 shadow-xs">
                            <span class="font-mono text-[10px] bg-teal-700 px-1 py-0.5 rounded text-teal-100">F8</span>
                            Uang Pas
                        </button>

                        <!-- Full Payment Modal -->
                        <button type="button" 
                                @click="openPaymentModal()" 
                                :disabled="cart.length === 0 || isCheckingOut"
                                class="py-2.5 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs transition-colors flex items-center justify-center gap-1 shadow-sm shadow-emerald-600/20">
                            <span class="font-mono text-[10px] bg-emerald-700 px-1 py-0.5 rounded text-emerald-100">F9</span>
                            Bayar &rarr;
                        </button>
                    </div>

                </div>

            </div>

        </div>

        <!-- MODAL: Full Payment & Split Payment (F9) -->
        <div x-show="isPaymentModalOpen" 
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
             style="display: none;">
            
            <div @click.away="isPaymentModalOpen = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Pembayaran Transaksi</h3>
                        <p class="text-xs text-slate-500">Pilih metode pembayaran (Tunai, QRIS, Kartu Debit, dll)</p>
                    </div>
                    <button type="button" @click="isPaymentModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto space-y-5">
                    
                    <!-- Bill Summary Card -->
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Total Tagihan Belanja</span>
                        <div class="text-3xl font-black text-emerald-700 tabular-nums mt-0.5" x-text="'Rp ' + Number(cartGrandTotal).toLocaleString('id-ID')"></div>
                    </div>

                    <!-- Customer Info -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pelanggan:</label>
                            <input type="text" x-model="customerName" placeholder="Pelanggan Umum" class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Struk:</label>
                            <input type="text" x-model="orderNotes" placeholder="Opsional.." class="w-full text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <!-- Supervisor Authorization Card for High Discount -->
                    <div x-show="isDiscountOverThreshold" class="p-3.5 rounded-2xl bg-purple-50 border border-purple-200 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-purple-900">
                            <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <span>Otorisasi Diskon Khusus Supervisor Dibutuhkan</span>
                        </div>
                        <p class="text-[11px] text-purple-700">
                            Diskon manual sebesar Rp <span x-text="Number(transactionDiscount).toLocaleString('id-ID')"></span> melebihi batas wajar kasir. Masukkan PIN Supervisor untuk melanjutkan.
                        </p>
                        <div>
                            <input type="password" 
                                   x-model="supervisorPin" 
                                   placeholder="PIN Supervisor (6 digit)..." 
                                   class="w-full text-center text-sm font-black tracking-widest rounded-xl border-purple-300 py-2 focus:border-purple-500 focus:ring-purple-500 bg-white">
                        </div>
                    </div>

                    <!-- Payment Rows (Supports Split Payment) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Pembayaran:</span>
                            <button type="button" @click="addPaymentRow()" class="text-xs text-indigo-600 font-bold hover:underline flex items-center gap-1">
                                + Tambah Metode Lain (Split)
                            </button>
                        </div>

                        <template x-for="(pay, pIdx) in payments" :key="pIdx">
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2">
                                <div class="flex items-center gap-2">
                                    <!-- Method Select -->
                                    <select x-model="pay.payment_method" class="w-1/2 text-xs font-bold rounded-lg border-slate-300 py-1.5 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                        <template x-for="m in paymentMethods" :key="m.value">
                                            <option :value="m.value" x-text="m.label"></option>
                                        </template>
                                    </select>

                                    <!-- Amount Input -->
                                    <div class="w-1/2 relative">
                                        <span class="absolute inset-y-0 left-2.5 flex items-center text-xs font-bold text-slate-400">Rp</span>
                                        <input type="number" 
                                               x-model.number="pay.amount" 
                                               min="0" 
                                               step="500" 
                                               class="w-full pl-8 pr-2 py-1.5 text-xs font-black rounded-lg border-slate-300 text-slate-900 tabular-nums focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <!-- Remove row if > 1 -->
                                    <button type="button" 
                                            x-show="payments.length > 1" 
                                            @click="removePaymentRow(pIdx)" 
                                            class="text-slate-400 hover:text-rose-600 p-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Reference Number (For Non-Cash) -->
                                <div x-show="pay.payment_method !== 'cash'">
                                    <input type="text" 
                                           x-model="pay.reference_number" 
                                           placeholder="Nomor Referensi / No. Bukti Transaksi / Ref QRIS (Opsional)" 
                                           class="w-full text-xs rounded-lg border-slate-300 py-1 px-2.5 text-slate-700">
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Quick Cash Chips -->
                    <div x-show="payments.length === 1 && payments[0].payment_method === 'cash'">
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pilihan Uang Cepat:</span>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button type="button" @click="setCashAmount(cartGrandTotal)" class="py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-800">
                                Uang Pas
                            </button>
                            <button type="button" @click="setCashAmount(roundUpTo(cartGrandTotal, 10000))" class="py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-800 tabular-nums">
                                + Rp 10.000
                            </button>
                            <button type="button" @click="setCashAmount(roundUpTo(cartGrandTotal, 50000))" class="py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-800 tabular-nums">
                                + Rp 50.000
                            </button>
                            <button type="button" @click="setCashAmount(roundUpTo(cartGrandTotal, 100000))" class="py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-800 tabular-nums">
                                + Rp 100.000
                            </button>
                        </div>
                    </div>

                    <!-- Change / Shortage Indicator -->
                    <div class="p-4 rounded-xl border"
                         :class="totalPaid >= cartGrandTotal ? 'bg-slate-50 border-slate-200' : 'bg-rose-50 border-rose-200 text-rose-900'">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold" x-text="totalPaid >= cartGrandTotal ? 'Kembalian Uang Kasir:' : 'Kekurangan Pembayaran:'"></span>
                            <span class="text-xl font-black tabular-nums"
                                  :class="totalPaid >= cartGrandTotal ? 'text-slate-900' : 'text-rose-600'"
                                  x-text="totalPaid >= cartGrandTotal ? 'Rp ' + Number(changeAmount).toLocaleString('id-ID') : '- Rp ' + Number(cartGrandTotal - totalPaid).toLocaleString('id-ID')">
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3">
                    <button type="button" @click="isPaymentModalOpen = false" class="px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors">
                        Batal (ESC)
                    </button>
                    <button type="button" 
                            @click="processCheckout()" 
                            :disabled="totalPaid < cartGrandTotal || isCheckingOut"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs transition-colors shadow-sm shadow-emerald-600/20 flex items-center gap-2">
                        <svg x-show="isCheckingOut" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Selesaikan Transaksi & Cetak Struk [Enter]</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- MODAL: Hold / Recall Cart (F4) -->
        <div x-show="isHoldModalOpen" 
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
             style="display: none;">
            
            <div @click.away="isHoldModalOpen = false" 
                 class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
                
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base text-slate-900">Antrean Keranjang Ditahan (Hold / Recall)</h3>
                        <p class="text-xs text-slate-500">Tahan keranjang untuk melayani antrean lain, atau muat kembali.</p>
                    </div>
                    <button type="button" @click="isHoldModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto space-y-6">
                    
                    <!-- Hold Current Cart Section -->
                    <div x-show="cart.length > 0" class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-3">
                        <h4 class="font-bold text-xs text-indigo-900">Tahan Keranjang Saat Ini:</h4>
                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="holdReference" 
                                   placeholder="Nama pelanggan / nomor antrean.." 
                                   class="flex-1 text-xs rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" 
                                    @click="submitHoldCart()" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                                Tahan Transaksi
                            </button>
                        </div>
                    </div>

                    <!-- List of Held Carts -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-slate-500">Daftar Antrean Tertahan:</h4>
                        
                        <div class="space-y-2">
                            <template x-for="held in heldList" :key="held.id">
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-indigo-200 flex items-center justify-between gap-3 shadow-2xs">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-xs text-slate-900" x-text="held.reference"></span>
                                            <span class="text-[10px] text-slate-400 font-mono" x-text="'(' + held.cart_items.length + ' item)'"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5" x-text="held.customer_name ? 'Pelanggan: ' + held.customer_name : ''"></div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                @click="recallCart(held.id)" 
                                                class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200 transition-colors">
                                            Muat Kembali &rarr;
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="heldList.length === 0" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada antrean yang ditahan saat ini.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button type="button" @click="isHoldModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-xs hover:bg-slate-50">
                        Tutup
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- Alpine.js POS Component Logic -->
    <script>
        function posTerminal(config) {
            return {
                products: config.initialProducts || [],
                paymentMethods: config.paymentMethods || [],
                heldList: config.heldCarts || [],
                searchQuery: '',
                selectedCategoryId: null,
                cart: [],
                transactionDiscount: 0,
                customerName: 'Pelanggan Umum',
                orderNotes: '',
                isPaymentModalOpen: false,
                isHoldModalOpen: false,
                holdReference: '',
                isCheckingOut: false,
                supervisorPin: '',
                payments: [{ payment_method: 'cash', amount: 0, reference_number: '' }],

                get isDiscountOverThreshold() {
                    const disc = parseFloat(this.transactionDiscount) || 0;
                    if (disc <= 0) return false;
                    const maxAllowed = Math.min(this.cartSubtotal * 0.05, 20000);
                    return disc > maxAllowed;
                },

                get filteredProducts() {
                    let list = this.products;
                    if (this.selectedCategoryId !== null) {
                        list = list.filter(p => p.category_id === this.selectedCategoryId);
                    }
                    if (this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase().trim();
                        list = list.filter(p => 
                            p.name.toLowerCase().includes(q) || 
                            (p.sku && p.sku.toLowerCase().includes(q)) ||
                            (p.barcode && p.barcode.toLowerCase().includes(q)) ||
                            p.units.some(u => u.barcode && u.barcode.toLowerCase().includes(q))
                        );
                    }
                    return list;
                },

                get cartTotalQty() {
                    return this.cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
                },

                get cartSubtotal() {
                    return this.cart.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
                },

                get cartGrandTotal() {
                    const disc = parseFloat(this.transactionDiscount) || 0;
                    return Math.max(0, this.cartSubtotal - disc);
                },

                get totalPaid() {
                    return this.payments.reduce((sum, p) => sum + (parseFloat(p.amount) || 0), 0);
                },

                get changeAmount() {
                    return Math.max(0, this.totalPaid - this.cartGrandTotal);
                },

                filterCategory(catId) {
                    this.selectedCategoryId = catId;
                },

                addProductToCart(product) {
                    const existingIndex = this.cart.findIndex(i => i.product_id === product.id && i.product_unit_id === null);

                    if (existingIndex > -1) {
                        this.cart[existingIndex].quantity += 1;
                        this.calculateLineSubtotal(existingIndex);
                    } else {
                        const newItem = {
                            product_id: product.id,
                            product_name: product.name,
                            sku: product.sku,
                            current_stock: product.current_stock,
                            base_unit: product.base_unit,
                            available_units: product.units || [],
                            product_unit_id: null,
                            unit_name: product.base_unit,
                            conversion_factor: 1,
                            unit_price: parseFloat(product.selling_price),
                            quantity: 1,
                            discount_amount: 0,
                            subtotal: parseFloat(product.selling_price)
                        };
                        this.cart.push(newItem);
                    }
                },

                onUnitChanged(index) {
                    const item = this.cart[index];
                    if (!item.product_unit_id) {
                        const product = this.products.find(p => p.id === item.product_id);
                        item.unit_name = item.base_unit;
                        item.conversion_factor = 1;
                        item.unit_price = parseFloat(product ? product.selling_price : item.unit_price);
                    } else {
                        const unit = item.available_units.find(u => u.id == item.product_unit_id);
                        if (unit) {
                            item.unit_name = unit.unit_name;
                            item.conversion_factor = parseInt(unit.conversion_factor);
                            item.unit_price = parseFloat(unit.selling_price);
                        }
                    }
                    this.calculateLineSubtotal(index);
                },

                updateQty(index, delta) {
                    const newQty = (this.cart[index].quantity || 1) + delta;
                    if (newQty <= 0) {
                        this.removeItem(index);
                    } else {
                        this.cart[index].quantity = newQty;
                        this.calculateLineSubtotal(index);
                    }
                },

                calculateLineSubtotal(index) {
                    const item = this.cart[index];
                    const qty = Math.max(1, parseInt(item.quantity) || 1);
                    const price = parseFloat(item.unit_price) || 0;
                    const disc = parseFloat(item.discount_amount) || 0;
                    item.subtotal = Math.max(0, (qty * price) - disc);
                },

                removeItem(index) {
                    this.cart.splice(index, 1);
                },

                clearCart() {
                    if (confirm('Kosongkan semua item dalam keranjang belanja?')) {
                        this.cart = [];
                        this.transactionDiscount = 0;
                    }
                },

                handleBarcodeScan() {
                    const code = this.searchQuery.trim();
                    if (!code) return;

                    // Search product matching exact barcode or sku
                    let matchedProduct = this.products.find(p => p.barcode === code || p.sku === code);
                    let matchedUnitId = null;

                    if (!matchedProduct) {
                        // Check unit barcodes
                        for (const p of this.products) {
                            const unitMatch = p.units.find(u => u.barcode === code);
                            if (unitMatch) {
                                matchedProduct = p;
                                matchedUnitId = unitMatch.id;
                                break;
                            }
                        }
                    }

                    if (matchedProduct) {
                        this.addProductToCart(matchedProduct);
                        if (matchedUnitId) {
                            const lastIdx = this.cart.length - 1;
                            this.cart[lastIdx].product_unit_id = matchedUnitId;
                            this.onUnitChanged(lastIdx);
                        }
                        this.searchQuery = '';
                    }
                },

                openPaymentModal() {
                    if (this.cart.length === 0) return;
                    this.payments = [{ payment_method: 'cash', amount: this.cartGrandTotal, reference_number: '' }];
                    this.isPaymentModalOpen = true;
                },

                quickPayExact() {
                    if (this.cart.length === 0 || this.isCheckingOut) return;
                    if (this.isDiscountOverThreshold && !this.supervisorPin) {
                        this.openPaymentModal();
                        alert('Diskon manual melebihi batas wajar kasir. Otorisasi PIN Supervisor diperlukan.');
                        return;
                    }
                    this.payments = [{ payment_method: 'cash', amount: this.cartGrandTotal, reference_number: '' }];
                    this.processCheckout();
                },

                addPaymentRow() {
                    this.payments.push({ payment_method: 'qris', amount: 0, reference_number: '' });
                },

                removePaymentRow(index) {
                    if (this.payments.length > 1) {
                        this.payments.splice(index, 1);
                    }
                },

                setCashAmount(amount) {
                    if (this.payments.length > 0) {
                        this.payments[0].amount = amount;
                    }
                },

                roundUpTo(num, step) {
                    return Math.ceil(num / step) * step;
                },

                openHoldModal() {
                    this.isHoldModalOpen = true;
                },

                closeModals() {
                    this.isPaymentModalOpen = false;
                    this.isHoldModalOpen = false;
                },

                async processCheckout() {
                    if (this.cart.length === 0 || this.totalPaid < this.cartGrandTotal) return;

                    if (this.isDiscountOverThreshold && !this.supervisorPin) {
                        alert('Diskon manual melebihi batas wajar kasir. Silakan masukkan PIN Supervisor pada form pembayaran.');
                        return;
                    }

                    this.isCheckingOut = true;

                    const payload = {
                        cart_items: this.cart.map(i => ({
                            product_id: i.product_id,
                            product_unit_id: i.product_unit_id || null,
                            quantity: i.quantity,
                            discount_amount: i.discount_amount || 0
                        })),
                        payments: this.payments.map(p => ({
                            payment_method: p.payment_method,
                            amount: p.amount,
                            reference_number: p.reference_number || null
                        })),
                        customer_name: this.customerName || 'Pelanggan Umum',
                        discount_amount: this.transactionDiscount || 0,
                        supervisor_pin: this.supervisorPin || null,
                        notes: this.orderNotes || null
                    };

                    try {
                        const response = await fetch(config.checkoutUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const res = await response.json();

                        if (response.ok && res.success) {
                            window.location.href = res.receipt_url;
                        } else {
                            alert(res.message || 'Terjadi kesalahan saat memproses pembayaran.');
                        }
                    } catch (e) {
                        alert('Koneksi terganggu. Silakan periksa kembali transaksi.');
                    } finally {
                        this.isCheckingOut = false;
                    }
                },

                async submitHoldCart() {
                    if (this.cart.length === 0) return;

                    const payload = {
                        reference: this.holdReference || 'Antrean #' + (this.heldList.length + 1),
                        customer_name: this.customerName || null,
                        cart_items: this.cart,
                        discount_amount: this.transactionDiscount || 0,
                        notes: this.orderNotes || null
                    };

                    try {
                        const response = await fetch(config.holdUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            },
                            body: JSON.stringify(payload)
                        });

                        const res = await response.json();
                        if (response.ok && res.success) {
                            this.heldList.unshift(res.held_cart);
                            this.cart = [];
                            this.transactionDiscount = 0;
                            this.holdReference = '';
                            this.isHoldModalOpen = false;
                            alert(res.message);
                        } else {
                            alert(res.message || 'Gagal menahan transaksi.');
                        }
                    } catch (e) {
                        alert('Koneksi bermasalah.');
                    }
                },

                async recallCart(heldId) {
                    try {
                        const response = await fetch(config.recallUrl + '/' + heldId, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            }
                        });

                        const res = await response.json();
                        if (response.ok && res.success) {
                            this.cart = res.data.cart_items || [];
                            this.transactionDiscount = res.data.discount_amount || 0;
                            this.customerName = res.data.customer_name || 'Pelanggan Umum';
                            this.orderNotes = res.data.notes || '';
                            this.heldList = this.heldList.filter(h => h.id != heldId);
                            this.isHoldModalOpen = false;
                        } else {
                            alert(res.message || 'Gagal memanggil keranjang.');
                        }
                    } catch (e) {
                        alert('Koneksi bermasalah.');
                    }
                }
            };
        }
    </script>
</x-layouts.app>
