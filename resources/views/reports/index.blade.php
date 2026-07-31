<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[#F8FAFC] overflow-x-hidden w-full">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Konten Utama -->
        <div class="flex-1 w-full md:ml-[280px] flex flex-col min-h-screen overflow-x-hidden">
            <header class="h-[80px] bg-white border-b border-slate-200 px-4 sm:px-8 flex justify-between items-center sticky top-0 z-40 w-full">
                <div class="flex items-center gap-3 w-full sm:w-96">
                    <!-- Tombol Garis Tiga Sidebar Mobile -->
                    <button @click="sidebarOpen = true; window.dispatchEvent(new CustomEvent('toggle-sidebar'))" class="md:hidden p-2.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 focus:outline-none shrink-0 flex items-center justify-center">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>

                    <!-- Kolom Pencarian (Sudah diubah menjadi form agar berfungsi) -->
                    <form action="{{ route('reports.index') }}" method="GET" class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari transaksi laporan..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm bg-slate-50/50">
                        
                        <!-- Mempertahankan filter lain saat melakukan pencarian -->
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <input type="hidden" name="sort" value="{{ $sort ?? 'tanggal_asc' }}">
                    </form>
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

            <main class="p-4 sm:p-8 space-y-6 flex-1 w-full max-w-full overflow-x-hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Laporan Keuangan</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Ringkasan transaksi dan rekapitulasi bulanan Anda.</p>
                    </div>

                    <!-- Filter Bulan, Tahun, Urutan & Tombol Cetak -->
                    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        <select name="bulan" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 focus:border-red-500 bg-white text-slate-800 font-medium cursor-pointer">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select name="tahun" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 focus:border-red-500 bg-white text-slate-800 font-medium cursor-pointer">
                            <option value="2026" {{ $tahun == '2026' ? 'selected' : '' }}>2026</option>
                            <option value="2025" {{ $tahun == '2025' ? 'selected' : '' }}>2025</option>
                        </select>

                        <!-- Dropdown Sort (Urutkan Berdasarkan) -->
                        <select name="sort" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 focus:border-red-500 bg-white text-slate-800 font-medium cursor-pointer">
                            <option value="tanggal_asc" {{ $sort == 'tanggal_asc' ? 'selected' : '' }}>Waktu (Lama - Baru)</option>
                            <option value="tanggal_desc" {{ $sort == 'tanggal_desc' ? 'selected' : '' }}>Waktu (Baru - Lama)</option>
                            <option value="kategori" {{ $sort == 'kategori' ? 'selected' : '' }}>Berdasarkan Kategori (A-Z)</option>
                            <option value="terbesar_masuk" {{ $sort == 'terbesar_masuk' ? 'selected' : '' }}>Uang Masuk Terbesar</option>
                            <option value="terbesar_keluar" {{ $sort == 'terbesar_keluar' ? 'selected' : '' }}>Uang Keluar Terbesar</option>
                        </select>

                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-2 rounded-xl text-xs font-semibold transition">
                            Tampilkan
                        </button>
                        
                        <button type="button" onclick="window.print()" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-xl text-xs font-semibold transition">
                            Cetak Laporan
                        </button>
                    </form>
                </div>

                <!-- Ringkasan Statistik Kartu -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
                    <x-card>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Pemasukan</p>
                        <h3 class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</h3>
                    </x-card>
                    <x-card>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Pengeluaran</p>
                        <h3 class="text-2xl font-bold text-red-500">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</h3>
                    </x-card>
                    <x-card>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Saldo Bersih</p>
                        <h3 class="text-2xl font-bold {{ $saldoBersih >= 0 ? 'text-slate-800' : 'text-red-600' }}">Rp {{ number_format($saldoBersih, 0, ',', '.') }}</h3>
                    </x-card>
                </div>

                <!-- Tabel Rincian Transaksi Periode Ini -->
                <x-card class="w-full overflow-hidden">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Rincian Transaksi Periode Ini</h3>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase border-b border-slate-200">
                                    <th class="py-3 px-4 rounded-l-xl">Deskripsi</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4 text-right">Uang Masuk</th>
                                    <th class="py-3 px-4 text-right rounded-r-xl">Uang Keluar</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-100">
                                @forelse($transactions as $trx)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <!-- Deskripsi -->
                                        <td class="py-4 px-4 font-semibold text-slate-800">{{ $trx->deskripsi }}</td>
                                        
                                        <!-- Kategori -->
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ $trx->category->nama ?? '-' }}
                                            </span>
                                        </td>

                                        <!-- Tanggal -->
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 text-sm">Tidak ada transaksi tercatat pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </main>
        </div>
    </div>
</x-app-layout>