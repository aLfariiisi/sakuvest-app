<aside class="w-[280px] bg-white border-r border-slate-200 hidden md:flex flex-col justify-between shrink-0 fixed inset-y-0 z-50">
    <div>
        <!-- Logo Brand -->
        <div class="h-[80px] flex items-center px-8 border-b border-slate-100">
            <span class="text-xl font-bold text-red-500 flex items-center gap-3">
                <i data-lucide="wallet" class="w-7 h-7 text-red-500"></i>
                Sakuvest
            </span>
        </div>

        <!-- Menu Navigasi -->
        <div class="px-4 py-6">
            <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Menu Utama</p>
            <nav class="space-y-1.5">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl {{ request()->routeIs('dashboard') ? 'bg-red-50 text-red-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Dashboard
                </a>
                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('categories.*') ? 'bg-red-50 text-red-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition">
                    <i data-lucide="tags" class="w-5 h-5"></i>
                    Kategori
                </a>
                <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('transactions.*') ? 'bg-red-50 text-red-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition">
                    <i data-lucide="arrow-left-right" class="w-5 h-5"></i>
                    Transaksi
                </a>
                <a href="{{ route('saving-targets.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('saving-targets.*') ? 'bg-red-50 text-red-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition">
                    <i data-lucide="piggy-bank" class="w-5 h-5"></i>
                    Target Tabungan
                </a>
                <a href="{{ route('budgets.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('budgets.*') ? 'bg-red-50 text-red-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition">
                    <i data-lucide="chart-column" class="w-5 h-5"></i>
                    Anggaran Bulanan
                </a>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('reports.*') ? 'bg-red-50 text-red-500' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }} transition">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Laporan
                </a>
            </nav>
        </div>
    </div>

    <!-- Info User di Bawah Sidebar -->
    <div class="p-4 border-t border-slate-100 m-4 bg-slate-50 rounded-2xl flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-bold shrink-0">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Logout" class="text-slate-400 hover:text-red-500 transition">
                <i data-lucide="log-out" class="w-5 h-5"></i>
            </button>
        </form>
    </div>
</aside>