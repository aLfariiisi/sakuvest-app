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

            <main class="p-4 sm:p-8 space-y-6 flex-1 w-full max-w-full overflow-x-hidden">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Laporan Keuangan</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Ringkasan transaksi dan rekapitulasi bulanan Anda.</p>
                    </div>

                    <!-- Filter Bulan & Tahun + Tombol Cetak -->
                    <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <select name="bulan" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 focus:border-red-500 bg-white text-slate-800 font-medium">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select name="tahun" class="rounded-xl border border-slate-200 text-xs py-2 px-3 focus:ring-red-500 focus:border-red-500 bg-white text-slate-800 font-medium">
                            <option value="2026" {{ $tahun == '2026' ? 'selected' : '' }}>2026</option>
                            <option value="2025" {{ $tahun == '2025' ? 'selected' : '' }}>2025</option>
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
                        <x-table class="w-full min-w-[600px]">
                            <thead>
                                <tr class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase border-b border-slate-200">
                                    <th class="py-3 px-4 rounded-l-xl">Tanggal</th>
                                    <th class="py-3 px-4">Deskripsi</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4">Tipe</th>
                                    <th class="py-3 px-4 text-right rounded-r-xl">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-100">
                                @forelse($transactions as $trx)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-4 px-4 text-slate-400 text-xs">{{ date('d M Y', strtotime($trx->tanggal)) }}</td>
                                        <td class="py-4 px-4 font-semibold text-slate-800">{{ $trx->deskripsi }}</td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                                {{ $trx->category->nama ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4">
                                            <x-badge :type="$trx->tipe">
                                                {{ ucfirst($trx->tipe) }}
                                            </x-badge>
                                        </td>
                                        <td class="py-4 px-4 text-right font-bold {{ $trx->tipe == 'masuk' ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ $trx->tipe == 'masuk' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 text-sm">Tidak ada transaksi tercatat pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-table>
                    </div>
                </x-card>
            </main>
        </div>
    </div>
</x-app-layout>