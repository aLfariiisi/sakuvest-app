<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[#F8FAFC]">
        <x-sidebar />

        <div class="flex-1 md:ml-[280px] flex flex-col min-h-screen">
            <!-- Navbar -->
            <header class="h-[80px] bg-white border-b border-slate-200 px-4 sm:px-8 flex justify-between items-center sticky top-0 z-40 w-full">
                <div class="flex items-center gap-3 w-full sm:w-96">
                    <button @click="$dispatch('toggle-sidebar')" class="md:hidden p-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-none shrink-0 flex items-center justify-center">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    
                    <form action="{{ url()->current() }}" method="GET" class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari transaksi..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm bg-slate-50/50">
                        @if(request('bulan')) <input type="hidden" name="bulan" value="{{ request('bulan') }}"> @endif
                        @if(request('tahun')) <input type="hidden" name="tahun" value="{{ request('tahun') }}"> @endif
                    </form>
                </div>

                <div class="flex items-center gap-4 shrink-0">
                    <div class="w-10 h-10 rounded-xl bg-red-500 text-white flex items-center justify-center font-bold shadow-sm shadow-red-500/20">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            <main class="p-8 space-y-6 flex-1">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Transaksi Keuangan</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Catat dan pantau seluruh arus kas Anda di sini.</p>
                    </div>

                    <!-- Filter Bulan & Tahun -->
                    <form method="GET" action="{{ route('transactions.index') }}" class="flex items-center gap-2">
                        <select name="bulan" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 bg-white">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <select name="tahun" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 bg-white">
                            <option value="2026" {{ $tahun == '2026' ? 'selected' : '' }}>2026</option>
                            <option value="2025" {{ $tahun == '2025' ? 'selected' : '' }}>2025</option>
                        </select>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-xl text-xs font-semibold">
                            Filter
                        </button>
                    </form>
                </div>

                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl text-sm font-medium">
                        {{ session('success') }}
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
                    <!-- FORM TAMBAH TRANSAKSI -->
                    <x-card class="h-fit">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Catat Transaksi</h3>
                        <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori</label>
                                <select name="category_id" id="category_select" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 text-sm py-3 px-4 bg-slate-50/50" onchange="toggleCategoryInput(this)">
                                    <!-- OPSI DEFAULT KOSONG -->
                                    <option value="" disabled selected>Pilih Kategori</option>
                                    
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-tipe="{{ $cat->tipe }}">{{ $cat->nama }} ({{ ucfirst($cat->tipe) }})</option>
                                    @endforeach
                                    <option value="new" class="font-bold text-red-600">+ Tambah Kategori Baru...</option>
                                </select>

                                <!-- Input Kategori Baru -->
                                <div id="new_category_container" class="mt-3" style="display: none;">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kategori Baru</label>
                                    <input type="text" name="new_category_nama" id="new_category_input" placeholder="Contoh: Belanja Bulanan" class="w-full rounded-xl border-slate-200 text-sm py-2.5 px-4 bg-red-50/30">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Transaksi</label>
                                <select name="tipe" id="tipe_select" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 text-sm py-3 px-4 bg-slate-50/50">
                                    <!-- OPSI DEFAULT KOSONG -->
                                    <option value="" disabled selected>Pilih Tipe Transaksi</option>
                                    <option value="masuk">Pemasukan (Masuk)</option>
                                    <option value="keluar">Pengeluaran (Keluar)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal (Rp)</label>
                                <input type="number" name="nominal" placeholder="Contoh: 50000" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 text-sm py-3 px-4 bg-slate-50/50">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal</label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 text-sm py-3 px-4 bg-slate-50/50">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi / Catatan</label>
                                <textarea name="deskripsi" rows="2" placeholder="Tulis rincian transaksi..." required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 text-sm py-3 px-4 bg-slate-50/50"></textarea>
                            </div>

                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm transition">
                                Simpan Transaksi
                            </button>
                        </form>
                    </x-card>

                    <!-- TABEL DAFTAR TRANSAKSI -->
                    <x-card class="lg:col-span-2">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Riwayat Transaksi</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[600px]">
                                <thead>
                                    <tr class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase border-b border-slate-200">
                                        <th class="py-3 px-4 rounded-l-xl">Tanggal</th>
                                        <th class="py-3 px-4">Deskripsi</th>
                                        <th class="py-3 px-4">Kategori & Tipe</th>
                                        <th class="py-3 px-4 text-right">Nominal</th>
                                        <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-100">
                                    @forelse($transactions as $trx)
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="py-4 px-4 text-slate-500 text-xs">{{ date('d M Y', strtotime($trx->tanggal)) }}</td>
                                            <td class="py-4 px-4 font-semibold text-slate-800">{{ $trx->deskripsi }}</td>
                                            <td class="py-4 px-4">
                                                <div class="flex flex-col gap-1 items-start">
                                                    <span class="text-xs font-bold text-slate-700">{{ $trx->category->nama ?? '-' }}</span>
                                                    <x-badge :type="$trx->tipe">
                                                        {{ ucfirst($trx->tipe) }}
                                                    </x-badge>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-right font-bold {{ $trx->tipe == 'masuk' ? 'text-emerald-600' : 'text-red-500' }}">
                                                {{ $trx->tipe == 'masuk' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                @if(str_starts_with($trx->deskripsi, 'Setor Tabungan:') || str_starts_with($trx->deskripsi, 'Penarikan Tabungan:') || str_starts_with($trx->deskripsi, 'Refund Tabungan'))
                                                    <span class="text-[10px] bg-slate-100 text-slate-400 px-2 py-1 rounded-md font-semibold cursor-not-allowed" title="Transaksi sistem tidak bisa dihapus">Terkunci</span>
                                                @else
                                                    <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-slate-400 hover:text-red-600 font-semibold text-xs transition">Hapus</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">Belum ada transaksi di periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                </div>
            </main>
        </div>
    </div>

    <!-- LOGIKA JAVASCRIPT UNTUK DROPDOWN -->
    <script>
        function toggleCategoryInput(selectElement) {
            const selectedValue = selectElement.value;
            const newCatContainer = document.getElementById('new_category_container');
            const newCatInput = document.getElementById('new_category_input');
            const tipeSelect = document.getElementById('tipe_select');

            if (selectedValue === 'new') {
                // Jika pilih "+ Tambah Kategori Baru"
                newCatContainer.style.display = 'block';
                newCatInput.required = true;
                newCatInput.value = '';

                // Buka kunci tipe dan reset ke "Pilih Tipe Transaksi"
                tipeSelect.style.pointerEvents = 'auto';
                tipeSelect.className = "w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 text-sm py-3 px-4 bg-white";
                tipeSelect.value = ''; 
            } else if (selectedValue !== '') {
                // Jika pilih kategori yang sudah ada
                newCatContainer.style.display = 'none';
                newCatInput.required = false;

                // Ambil data tipe dari opsi kategori yang dipilih
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const kategoriTipe = selectedOption.getAttribute('data-tipe');

                // Set value tipe dan KUNCI dropdown-nya (supaya user gak bisa ganti)
                tipeSelect.value = kategoriTipe;
                tipeSelect.style.pointerEvents = 'none'; // Kunci interaksi mouse/klik
                tipeSelect.className = "w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-100 text-slate-500 cursor-not-allowed";
            }
        }
    </script>
</x-app-layout>