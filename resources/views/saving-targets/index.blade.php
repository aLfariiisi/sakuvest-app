<x-app-layout>
    <div x-data="{ 
        sidebarOpen: false,
        withdrawModalOpen: false,
        targetId: '',
        namaTarget: '',
        terkumpul: 0,
        tarikNominal: '',
        openWithdrawModal(target) {
            this.targetId = target.id;
            this.namaTarget = target.nama_target;
            this.terkumpul = target.terkumpul;
            this.tarikNominal = '';
            this.withdrawModalOpen = true;
        }
    }" class="min-h-screen flex bg-[#F8FAFC]">
        <x-sidebar />

        <div class="flex-1 md:ml-[280px] flex flex-col min-h-screen">
            <!-- Navbar dengan Tombol Menu Mobile yang Aktif -->
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
                    <h1 class="text-2xl font-bold text-slate-900">Target Tabungan</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Kelola dan pantau progres target impian finansial Anda.</p>
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
                    <!-- Form Buat Target Baru -->
                    <x-card class="h-fit">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Buat Target Baru</h3>
                        
                        <form action="{{ route('saving-targets.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Target</label>
                                <input type="text" name="nama_target" placeholder="Contoh: Beli Laptop Baru" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Target Nominal (Rp)</label>
                                <input type="number" name="target_nominal" placeholder="Contoh: 10000000" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Terkumpul Awal (Rp)</label>
                                <input type="number" name="terkumpul" placeholder="Contoh: 500000" value="0" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                            </div>

                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 px-5 rounded-xl text-sm font-semibold transition">
                                Simpan Target Tabungan
                            </button>
                        </form>
                    </x-card>

                    <!-- Daftar Kartu Progres & Aksi -->
                    <div class="lg:col-span-2 space-y-4">
                        <h3 class="text-lg font-bold text-slate-900">Daftar Target Aktif</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($savingTargets as $target)
                                <x-card class="flex flex-col justify-between space-y-4">
                                    <div>
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-bold text-slate-800 text-base">{{ $target->nama_target }}</h4>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="openWithdrawModal({{ json_encode($target) }})" class="text-indigo-600 hover:text-indigo-800 transition text-xs font-semibold">Tarik</button>
                                                <form action="{{ route('saving-targets.destroy', $target->id) }}" method="POST" onsubmit="return confirm('Hapus target tabungan ini? Sisa saldo akan dikembalikan ke dompet utama.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition text-xs font-semibold">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-500 font-medium">
                                            Terkumpul: <strong class="text-slate-700">Rp {{ number_format($target->terkumpul, 0, ',', '.') }}</strong> / Rp {{ number_format($target->target_nominal, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <div>
                                        <div class="flex justify-between text-xs font-semibold mb-1.5">
                                            <span class="text-slate-600">Progres</span>
                                            <span class="text-emerald-600">{{ $target->progress }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden mb-4">
                                            <div class="bg-emerald-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $target->progress }}%"></div>
                                        </div>

                                        <form action="{{ route('saving-targets.deposit', $target->id) }}" method="POST" class="flex gap-2">
                                            @csrf
                                            <input type="number" name="tambah_nominal" placeholder="Nominal setor..." required class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 rounded-xl text-xs font-semibold shrink-0 transition">
                                                Setor
                                            </button>
                                        </form>
                                    </div>
                                </x-card>
                            @empty
                                <div class="md:col-span-2">
                                    <x-card class="py-12 text-center text-slate-400 text-sm">
                                        Belum ada target tabungan yang dibuat.
                                    </x-card>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- MODAL POP-UP PENGAMBILAN SALDO -->
        <div x-show="withdrawModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
            <div @click.away="withdrawModalOpen = false" class="bg-white rounded-2xl p-6 w-full max-w-md space-y-4 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">Pengambilan Saldo Tabungan</h3>
                <p class="text-xs text-slate-500">Target: <strong class="text-slate-700" x-text="namaTarget"></strong></p>
                <p class="text-xs text-slate-500">Saldo Terkumpul Saat Ini: <strong class="text-emerald-600" x-text="'Rp ' + Number(terkumpul).toLocaleString('id-ID')"></strong></p>

                <form :action="'{{ url('saving-targets') }}/' + targetId + '/withdraw'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal yang Ingin Ditarik (Rp)</label>
                        <input type="number" name="tarik_nominal" x-model="tarikNominal" placeholder="Contoh: 150000" :max="terkumpul" min="1" required class="w-full rounded-xl border-slate-200 text-sm py-3 px-4">
                        <p class="text-[11px] text-slate-400 mt-1">Dana yang ditarik akan otomatis dikembalikan ke saldo dompet utama.</p>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="withdrawModalOpen = false" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100 transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm">Tarik Saldo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>