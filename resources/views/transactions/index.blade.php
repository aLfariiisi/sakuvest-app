<x-app-layout>
    <div x-data="{ 
        sidebarOpen: false,
        editModalOpen: false,
        trxId: '',
        deskripsi: '',
        categoryId: '',
        tipe: 'masuk',
        nominal: '',
        tanggal: '{{ date('Y-m-d') }}',
        openEditModal(trx) {
            this.trxId = trx.id;
            this.deskripsi = trx.deskripsi;
            this.categoryId = trx.category_id;
            this.tipe = trx.tipe;
            this.nominal = trx.nominal;
            this.tanggal = trx.tanggal;
            this.editModalOpen = true;
        }
    }" class="min-h-screen flex bg-[#F8FAFC]">
        
        <x-sidebar />

        <div class="flex-1 md:ml-[280px] flex flex-col min-h-screen">
            <header class="h-[80px] bg-white border-b border-slate-200 px-4 sm:px-8 flex justify-between items-center sticky top-0 z-40 w-full">
                <div class="flex items-center gap-3 w-full sm:w-96">
                    <button @click="$dispatch('toggle-sidebar')" class="md:hidden p-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-none shrink-0 flex items-center justify-center">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" placeholder="Cari transaksi atau data..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm bg-slate-50/50">
                    </div>
                </div>

                <div class="flex items-center gap-4 shrink-0">
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center font-bold shadow-sm shadow-red-500/20">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500">Administrator</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-8 space-y-6 flex-1">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Kelola Transaksi</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Catat dan pantau seluruh pemasukan serta pengeluaran Anda.</p>
                </div>

                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Tambah Transaksi -->
                    <x-card class="h-fit">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Tambah Transaksi</h3>
                        
                        <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                                <input type="text" name="deskripsi" placeholder="Contoh: Beli Makan Siang" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori</label>
                                <select name="category_id" id="category_select" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4" onchange="autoSetTipe(this)">
                                    <option value="">Tanpa Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-tipe="{{ $cat->tipe }}">
                                            {{ $cat->nama }} ({{ ucfirst($cat->tipe) }})
                                        </option>
                                    @endforeach
                                    <option value="new" class="font-bold text-red-600">+ Tambah Kategori Baru...</option>
                                </select>

                                <!-- Input Muncul Jika Pilih Kategori Baru -->
                                <div id="new_category_container" class="mt-3" style="display: none;">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kategori Baru</label>
                                    <input type="text" name="new_category_nama" id="new_category_input" placeholder="Contoh: Makan, Transport, dll" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-2.5 px-4 bg-red-50/30">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Transaksi</label>
                                <select id="tipe_select" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4 bg-slate-50/50 text-slate-800" onchange="manualSetTipe(this)">
                                    <option value="masuk">Pemasukan (Masuk)</option>
                                    <option value="keluar">Pengeluaran (Keluar)</option>
                                </select>
                                <input type="hidden" name="tipe" id="tipe_hidden" value="masuk">
                                <p class="text-xs text-slate-400 mt-1" id="tipe_info">Pilih kategori untuk otomatis, atau pilih manual jika tanpa kategori.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal (Rp)</label>
                                <input type="number" name="nominal" placeholder="Contoh: 50000" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal</label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                            </div>

                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                                Simpan Transaksi
                            </button>
                        </form>
                    </x-card>

                    <!-- Tabel Riwayat Transaksi -->
                    <x-card class="lg:col-span-2">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Riwayat Transaksi</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[650px]">
                                <thead>
                                    <tr class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase border-b border-slate-200">
                                        <th class="py-3 px-4 rounded-l-xl">Deskripsi</th>
                                        <th class="py-3 px-4">Kategori</th>
                                        <th class="py-3 px-4">Tanggal</th>
                                        <th class="py-3 px-4 text-right">Uang Masuk</th>
                                        <th class="py-3 px-4 text-right">Uang Keluar</th>
                                        <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-100">
                                    @forelse($transactions as $trx)
                                        @php
                                            $isSavings = str_starts_with($trx->deskripsi, 'Setor Tabungan:') || 
                                                         str_starts_with($trx->deskripsi, 'Penarikan Tabungan:') || 
                                                         str_starts_with($trx->deskripsi, 'Refund Tabungan') ||
                                                         optional($trx->category)->nama == 'Tabungan';
                                        @endphp
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="py-4 px-4 font-semibold text-slate-800">{{ $trx->deskripsi }}</td>
                                            <td class="py-4 px-4">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                                    {{ $trx->category->nama ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-slate-400 text-xs">{{ date('d M Y', strtotime($trx->tanggal)) }}</td>
                                            
                                            <!-- Uang Masuk -->
                                            <td class="py-4 px-4 text-right font-bold text-emerald-600">
                                                @if($trx->tipe == 'masuk')
                                                    Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                                @else
                                                    <span class="text-slate-300 font-normal">-</span>
                                                @endif
                                            </td>

                                            <!-- Uang Keluar -->
                                            <td class="py-4 px-4 text-right font-bold text-red-500">
                                                @if($trx->tipe == 'keluar')
                                                    Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                                @else
                                                    <span class="text-slate-300 font-normal">-</span>
                                                @endif
                                            </td>

                                            <td class="py-4 px-4 text-right">
                                                @if($isSavings)
                                                    <span class="text-[11px] text-slate-400 italic bg-slate-100 px-2.5 py-1 rounded-lg">Terkunci</span>
                                                @else
                                                    <div class="space-x-2 inline-block">
                                                        <button type="button" @click="openEditModal({{ json_encode($trx) }})" class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs transition">
                                                            Edit
                                                        </button>
                                                        <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-slate-400 hover:text-red-600 font-semibold text-xs transition">Hapus</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-8 text-center text-slate-400 text-sm">Belum ada transaksi tercatat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </main>
        </div>

        <!-- MODAL POP-UP EDIT TRANSAKSI -->
        <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
            <div @click.away="editModalOpen = false" class="bg-white rounded-2xl p-6 w-full max-w-md space-y-4 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">Edit Transaksi (Catat Sebagai Riwayat Baru)</h3>

                <form :action="'{{ url('transactions') }}/' + trxId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                        <input type="text" name="deskripsi" x-model="deskripsi" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori</label>
                        <select name="category_id" x-model="categoryId" class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nama }} ({{ ucfirst($cat->tipe) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Transaksi</label>
                        <select name="tipe" x-model="tipe" class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                            <option value="masuk">Pemasukan (Masuk)</option>
                            <option value="keluar">Pengeluaran (Keluar)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal (Rp)</label>
                        <input type="number" name="nominal" x-model="nominal" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal</label>
                        <input type="date" name="tanggal" x-model="tanggal" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-red-500 hover:bg-red-600 text-white transition shadow-sm">Simpan Sebagai Riwayat Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function autoSetTipe(categorySelect) {
            const selectedValue = categorySelect.value;
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const tipe = selectedOption.getAttribute('data-tipe');
            
            const tipeSelect = document.getElementById('tipe_select');
            const tipeHidden = document.getElementById('tipe_hidden');
            const tipeInfo = document.getElementById('tipe_info');
            const newCatContainer = document.getElementById('new_category_container');
            const newCatInput = document.getElementById('new_category_input');

            if (selectedValue === 'new') {
                newCatContainer.style.display = 'block';
                newCatInput.required = true;

                // Buka tipe transaksi agar bisa dipilih bebas untuk kategori baru
                tipeSelect.disabled = false;
                tipeSelect.className = "w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4 bg-slate-50/50 text-slate-800";
                tipeHidden.value = tipeSelect.value;
                tipeInfo.innerText = "Masukkan nama kategori baru dan tentukan tipe transaksinya.";
            } else {
                newCatContainer.style.display = 'none';
                newCatInput.required = false;
                newCatInput.value = '';

                if (tipe) {
                    tipeSelect.value = tipe;
                    tipeSelect.disabled = true;
                    tipeSelect.className = "w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-100 text-slate-600 cursor-not-allowed";
                    tipeHidden.value = tipe;
                    tipeInfo.innerText = "Tipe terkunci otomatis mengikuti kategori.";
                } else {
                    tipeSelect.disabled = false;
                    tipeSelect.className = "w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4 bg-slate-50/50 text-slate-800";
                    tipeHidden.value = tipeSelect.value;
                    tipeInfo.innerText = "Silakan pilih tipe transaksi secara manual karena tanpa kategori.";
                }
            }
        }

        function manualSetTipe(tipeSelect) {
            const tipeHidden = document.getElementById('tipe_hidden');
            tipeHidden.value = tipeSelect.value;
        }
    </script>
</x-app-layout>