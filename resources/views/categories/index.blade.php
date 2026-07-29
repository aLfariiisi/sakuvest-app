<x-app-layout>
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex bg-[#F8FAFC]">
        <!-- Memanggil Komponen Sidebar -->
        <x-sidebar />

        <!-- Konten Utama -->
        <div class="flex-1 md:ml-[280px] flex flex-col min-h-screen">
            <!-- Memanggil Komponen Navbar -->
            <x-navbar />

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

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Tambah Kategori Menggunakan Komponen Card -->
                    <x-card class="h-fit">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Tambah Kategori Baru</h3>
                        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori</label>
                                <input type="text" name="nama" placeholder="Contoh: Makanan, Gaji, Transport" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Kategori</label>
                                <select name="tipe" required class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                                    <option value="masuk">Pemasukan (Masuk)</option>
                                    <option value="keluar">Pengeluaran (Keluar)</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                                Simpan Kategori
                            </button>
                        </form>
                    </x-card>

                    <!-- Tabel Daftar Kategori Menggunakan Komponen Card -->
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
                                                <!-- Menggunakan Komponen Badge -->
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
</x-app-layout>