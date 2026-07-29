<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[#F8FAFC]">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Konten Utama -->
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

            <!-- Body Kategori -->
            <main class="p-8 space-y-6 flex-1">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Kelola Kategori</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Atur kategori pemasukan dan pengeluaran keuangan Anda di sini.</p>
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
                    <!-- Form Tambah / Pilih Kategori -->
                    <x-card class="h-fit">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Tambah Kategori Baru</h3>
                        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Kategori</label>
                                <select name="existing_category_id" id="category_select" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4" onchange="toggleCategoryInput(this)">
                                    <option value="new" class="font-bold text-red-600" selected>+ Tambah Kategori Baru...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" data-nama="{{ $cat->nama }}" data-tipe="{{ $cat->tipe }}">
                                            {{ $cat->nama }} ({{ ucfirst($cat->tipe) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Input Nama Kategori Baru (Muncul jika opsi tambah baru dipilih) -->
                            <div id="new_category_container">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori Baru</label>
                                <input type="text" name="nama" id="new_category_input" placeholder="Contoh: Makanan, Gaji, Transport" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Kategori</label>
                                <select name="tipe" id="tipe_select" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4 bg-slate-50/50 text-slate-800">
                                    <option value="masuk">Pemasukan (Masuk)</option>
                                    <option value="keluar">Pengeluaran (Keluar)</option>
                                </select>
                                <p class="text-xs text-slate-400 mt-1" id="tipe_info">Pilih tipe kategori baru.</p>
                            </div>

                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                                Simpan Kategori
                            </button>
                        </form>
                    </x-card>

                    <!-- Tabel Daftar Kategori -->
                    <x-card class="lg:col-span-2">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Daftar Kategori</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 text-xs font-semibold text-slate-400 uppercase border-b border-slate-200">
                                        <th class="py-3 px-4 rounded-l-xl">No</th>
                                        <th class="py-3 px-4">Nama Kategori</th>
                                        <th class="py-3 px-4">Tipe</th>
                                        <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-100">
                                    @forelse($categories as $index => $cat)
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="py-4 px-4 text-slate-500 font-medium">{{ $index + 1 }}</td>
                                            <td class="py-4 px-4 font-semibold text-slate-800">{{ $cat->nama }}</td>
                                            <td class="py-4 px-4">
                                                <x-badge :type="$cat->tipe">
                                                    {{ ucfirst($cat->tipe) }}
                                                </x-badge>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-slate-400 hover:text-red-600 font-semibold text-xs transition">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-slate-400 text-sm">Belum ada kategori yang ditambahkan.</td>
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

    <script>
        function toggleCategoryInput(selectElement) {
            const selectedValue = selectElement.value;
            const newCatContainer = document.getElementById('new_category_container');
            const newCatInput = document.getElementById('new_category_input');
            const tipeSelect = document.getElementById('tipe_select');
            const tipeInfo = document.getElementById('tipe_info');

            if (selectedValue === 'new') {
                newCatContainer.style.display = 'block';
                newCatInput.required = true;
                newCatInput.value = '';

                // Buka pilihan tipe agar bisa diatur manual untuk kategori baru
                tipeSelect.disabled = false;
                tipeSelect.className = "w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4 bg-slate-50/50 text-slate-800";
                tipeInfo.innerText = "Pilih tipe kategori baru.";
            } else {
                newCatContainer.style.display = 'none';
                newCatInput.required = false;

                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const kategoriNama = selectedOption.getAttribute('data-nama');
                const kategoriTipe = selectedOption.getAttribute('data-tipe');

                // Masukkan nama kategori yang dipilih ke input tersembunyi/input teks agar tetap terbaca backend saat submit
                newCatInput.value = kategoriNama;
                tipeSelect.value = kategoriTipe;
                tipeSelect.disabled = true;
                tipeSelect.className = "w-full rounded-xl border-slate-200 text-sm py-3 px-4 bg-slate-100 text-slate-600 cursor-not-allowed";
                tipeInfo.innerText = "Tipe terkunci otomatis mengikuti kategori yang dipilih.";
            }
        }
    </script>
</x-app-layout>