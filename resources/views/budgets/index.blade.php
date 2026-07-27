<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[#F8FAFC]">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Konten Utama -->
        <div class="flex-1 md:ml-[280px] flex flex-col min-h-screen">
            <x-navbar />

            <main class="p-8 space-y-6 flex-1">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Anggaran Bulanan</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Atur batasan pengeluaran per kategori untuk mengontrol finansial Anda.</p>
                    </div>

                    <!-- Filter Bulan & Tahun -->
                    <form method="GET" action="{{ route('budgets.index') }}" class="flex flex-wrap items-center gap-2">
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
                    <!-- Form Tambah Anggaran -->
                    <x-card class="h-fit">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Setel Anggaran Baru</h3>
                        <form action="{{ route('budgets.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="bulan" value="{{ $bulan }}">
                            <input type="hidden" name="tahun" value="{{ $tahun }}">

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori (Pengeluaran)</label>
                                <select name="category_id" required class="w-full rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4 bg-slate-50/50 text-slate-800">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Batas Limit (Rp)</label>
                                <input type="number" name="limit_nominal" placeholder="Contoh: 1500000" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                            </div>

                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                                Simpan Anggaran
                            </button>
                        </form>
                    </x-card>

                    <!-- Daftar Kartu Anggaran & Progress -->
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Daftar Anggaran Bulan Ini</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($budgets as $budget)
                                <x-card class="flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-bold text-slate-800 text-base">{{ $budget->category->nama ?? 'Kategori Dihapus' }}</h4>
                                            <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" onsubmit="return confirm('Hapus anggaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-400 hover:text-red-500 transition text-xs font-semibold">Hapus</button>
                                            </form>
                                        </div>
                                        <p class="text-xs text-slate-500 font-medium">
                                            Terpakai: <strong class="{{ $budget->terpakai > $budget->limit_nominal ? 'text-red-600' : 'text-slate-700' }}">Rp {{ number_format($budget->terpakai, 0, ',', '.') }}</strong> / Limit: Rp {{ number_format($budget->limit_nominal, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div>
                                        <div class="flex justify-between text-xs font-semibold mb-1.5">
                                            <span class="text-slate-600">Penggunaan</span>
                                            <span class="{{ $budget->persentase >= 90 ? 'text-red-500' : 'text-emerald-600' }}">{{ $budget->persentase }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                            <div class="h-2.5 rounded-full transition-all duration-500 {{ $budget->persentase >= 90 ? 'bg-red-500' : 'bg-red-400' }}" style="width: {{ $budget->persentase }}%"></div>
                                        </div>
                                    </div>
                                </x-card>
                            @empty
                                <div class="md:col-span-2">
                                    <x-card class="py-12 text-center text-slate-400 text-sm">
                                        Belum ada anggaran bulanan yang disetel untuk periode ini.
                                    </x-card>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-app-layout>