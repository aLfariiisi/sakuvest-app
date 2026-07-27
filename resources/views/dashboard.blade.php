<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[#F8FAFC]">
        <!-- Memanggil Komponen Sidebar Utama -->
        <x-sidebar />

        <!-- Konten Utama -->
        <div class="flex-1 md:ml-[280px] flex flex-col min-h-screen">
            <!-- Navbar -->
            <header class="h-[80px] bg-white border-b border-slate-200 px-8 flex justify-between items-center sticky top-0 z-40">
                <div class="flex items-center gap-4 w-96">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" placeholder="Cari transaksi atau data..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm bg-slate-50/50">
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="w-10 h-10 rounded-xl border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition relative">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    <div class="h-8 w-px bg-slate-200"></div>
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

            <!-- Body Dashboard -->
            <main class="p-8 space-y-6 flex-1">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Dashboard Keuangan</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Ringkasan aktivitas finansial dan laporan Sakuvest Anda.</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('transactions.index') }}" class="bg-red-500 hover:bg-red-600 text-white rounded-xl px-5 py-3 text-sm font-semibold shadow-sm shadow-red-500/25 transition flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Transaksi
                        </a>
                    </div>
                </div>

                <!-- 1. 4 Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Balance -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Saldo</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($saldo, 0, ',', '.') }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center">
                                <i data-lucide="wallet" class="w-6 h-6"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <i data-lucide="trending-up" class="w-4 h-4"></i> Aman terkendali
                        </div>
                    </div>

                    <!-- Total Income -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                            <i data-lucide="coins" class="w-4 h-4"></i> Pendapatan aktif
                        </div>
                    </div>

                    <!-- Total Expense -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center">
                                <i data-lucide="arrow-down-left" class="w-6 h-6"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-red-500">
                            <i data-lucide="receipt" class="w-4 h-4"></i> Pengeluaran bulan ini
                        </div>
                    </div>

                    <!-- Total Savings -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tabungan</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($totalTabungan, 0, ',', '.') }}</h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i data-lucide="piggy-bank" class="w-6 h-6"></i>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1.5 text-xs font-semibold text-amber-600">
                            <i data-lucide="credit-card" class="w-4 h-4"></i> Target terkumpul
                        </div>
                    </div>
                </div>

                <!-- 2. ApexCharts Statistik Bulanan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Statistik Arus Kas</h3>
                            <p class="text-xs text-slate-500">Grafik perbandingan pemasukan dan pengeluaran tahun {{ date('Y') }}</p>
                        </div>
                        <select class="rounded-xl border-slate-200 text-sm py-2 px-3 text-slate-600 bg-slate-50/50">
                            <option>Tahun Ini ({{ date('Y') }})</option>
                        </select>
                    </div>
                    <div id="chartStatistik" class="w-full h-72"></div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- 3. Recent Transaction -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 lg:col-span-2 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-slate-900">Transaksi Terbaru</h3>
                                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-red-500 hover:underline">Lihat Semua</a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase border-b border-slate-200">
                                            <th class="py-3 px-4 rounded-l-xl">Deskripsi</th>
                                            <th class="py-3 px-4">Kategori</th>
                                            <th class="py-3 px-4">Tanggal</th>
                                            <th class="py-3 px-4 text-right rounded-r-xl">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm divide-y divide-slate-100">
                                        @forelse($transaksiTerbaru as $trx)
                                            <tr class="hover:bg-slate-50/80 transition">
                                                <td class="py-4 px-4 font-semibold text-slate-800">{{ $trx->deskripsi }}</td>
                                                <td class="py-4 px-4 text-slate-500 font-medium">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                                        {{ $trx->category->nama ?? '-' }}
                                                    </span>
                                                </td>
                                                <td class="py-4 px-4 text-slate-400 text-xs">{{ date('d M Y', strtotime($trx->tanggal)) }}</td>
                                                <td class="py-4 px-4 text-right font-bold {{ $trx->tipe == 'masuk' ? 'text-emerald-600' : 'text-red-500' }}">
                                                    {{ $trx->tipe == 'masuk' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-8 text-center text-slate-400 text-sm">Belum ada transaksi tercatat.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Budget & 5. Saving Goals -->
                    <div class="space-y-6">
                        <!-- Anggaran Bulanan -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">Anggaran Bulanan</h3>
                            @forelse($budgets as $b)
                                <div class="mb-4 last:mb-0">
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="font-semibold text-slate-700">{{ $b->category->nama ?? 'Kategori' }}</span>
                                        <span class="text-xs text-slate-500 font-medium">Rp {{ number_format($b->sudah_dipakai, 0, ',', '.') }} / <strong class="text-slate-800">Rp {{ number_format($b->limit_nominal, 0, ',', '.') }}</strong></span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-red-500 h-2.5 rounded-full" style="width: {{ $b->progress }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada anggaran diatur bulan ini.</p>
                            @endforelse
                        </div>

                        <!-- Target Tabungan -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">Target Tabungan</h3>
                            @forelse($savingTargets as $target)
                                <div class="mb-4 last:mb-0">
                                    <div class="flex justify-between text-sm mb-1.5">
                                        <span class="font-semibold text-slate-700">{{ $target->nama_target }}</span>
                                        <span class="text-xs text-slate-500 font-medium">{{ $target->progress }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $target->progress }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 text-center py-4">Belum ada target tabungan.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Script ApexCharts Dinamis -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const options = {
                chart: {
                    type: 'area',
                    height: 280,
                    toolbar: { show: false },
                    fontFamily: 'Plus Jakarta Sans, sans-serif'
                },
                series: [{
                    name: 'Pemasukan',
                    data: @json($grafikMasuk)
                }, {
                    name: 'Pengeluaran',
                    data: @json($grafikKeluar)
                }],
                colors: ['#22C55E', '#EF4444'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: { opacityFrom: 0.4, opacityTo: 0.05 }
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    labels: { style: { colors: '#64748B', fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { colors: '#64748B', fontSize: '12px' } }
                },
                grid: { borderColor: '#E2E8F0', strokeDashArray: 4 },
                legend: { position: 'top', horizontalAlign: 'right' }
            };

            const chart = new ApexCharts(document.querySelector("#chartStatistik"), options);
            chart.render();
        });
    </script>
</x-app-layout>