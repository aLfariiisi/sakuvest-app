<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-[#F8FAFC] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
            
            <!-- Header Judul -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-slate-900">Selamat Datang Kembali</h2>
                <p class="text-sm text-slate-500 mt-1">Masuk untuk mengelola keuangan dan tabungan Anda.</p>
            </div>

            <!-- Status Session -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Error Validation -->
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-4" method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@email.com" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="w-full rounded-xl border-slate-200 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm py-3 px-4">
                </div>

                <!-- Remember Me & Lupa Password -->
                <div class="flex items-center justify-between text-sm pt-1">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-red-500 focus:ring-red-500">
                        <span class="ml-2 text-slate-600 font-medium">Ingat Saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-red-500 hover:text-red-600 font-semibold transition" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <!-- Tombol Masuk -->
                <div class="pt-2">
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-3 px-5 rounded-xl text-sm font-semibold shadow-sm shadow-red-500/25 transition">
                        Masuk
                    </button>
                </div>

                <!-- Link Registrasi (Jika ada) -->
                @if (Route::has('register'))
                    <div class="text-center text-sm text-slate-500 pt-2">
                        Belum punya akun? 
                        <a href="{{ route('register') }}" class="text-red-500 hover:text-red-600 font-semibold transition">Daftar sekarang</a>
                    </div>
                @endif
            </form>
        </div>
    </div>
</x-guest-layout>