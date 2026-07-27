<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#F8FAFC] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
            
            <div class="text-center">
                <h2 class="text-2xl font-bold text-slate-900">Lupa Password?</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan email Anda dan kami akan mengirimkan tautan untuk mereset password Anda.</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-4" method="POST" action="{{ route('password.email') }}">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                        Kirim Tautan Reset Password
                    </button>
                </div>

                <div class="text-center text-sm text-slate-500 pt-2">
                    <a href="{{ route('login') }}" class="text-red-500 hover:text-red-600 font-semibold transition">Kembali ke halaman login</a>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>