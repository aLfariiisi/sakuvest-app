<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#F8FAFC] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
            
            <div class="text-center">
                <h2 class="text-2xl font-bold text-slate-900">Verifikasi Email</h2>
                <p class="text-sm text-slate-500 mt-1">Terima kasih telah mendaftar! Sebelum mulai, mohon verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan ke email Anda.</p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium text-center">
                    Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.
                </div>
            @endif

            <div class="flex flex-col gap-3 pt-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                        Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 px-5 rounded-xl text-sm font-semibold transition">
                        Keluar (Log Out)
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>