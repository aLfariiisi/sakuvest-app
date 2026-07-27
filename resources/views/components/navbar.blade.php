<header class="h-[80px] bg-white border-b border-slate-200 px-8 flex justify-between items-center sticky top-0 z-40">
    <div class="flex items-center gap-4 w-96">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </span>
            <input type="text" placeholder="Cari data atau transaksi..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm bg-slate-50/50">
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