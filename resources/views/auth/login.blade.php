<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IT Helpdesk PTPN IV</title>
    <script>
        const theme = localStorage.getItem('user-theme') ?? 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#F3F4F6] px-4 py-10 font-sans dark:bg-gray-900">
    <div class="pointer-events-none fixed inset-0">
        <div class="absolute -left-[10%] -top-[10%] h-[40%] w-[40%] rounded-full bg-blue-400/30 blur-[100px] dark:bg-blue-600/20"></div>
        <div class="absolute -bottom-[10%] -right-[10%] h-[40%] w-[40%] rounded-full bg-indigo-400/30 blur-[100px] dark:bg-indigo-600/20"></div>
    </div>
    <main class="relative z-10 w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-3 text-center">
            <img src="{{ asset('img/logo-ptpn.png') }}" alt="Logo PTPN" class="h-14 w-auto">
            <div class="text-left"><h1 class="text-2xl font-bold text-gray-900 dark:text-white">IT Helpdesk</h1><p class="text-sm font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">PTPN IV</p></div>
        </div>
        <section class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white/90 px-6 py-8 shadow-xl backdrop-blur sm:px-10 dark:border-gray-700/50 dark:bg-gray-800/90">
            <div class="absolute left-0 top-0 h-1.5 w-full bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
            <a href="{{ route('home') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400">&larr; Kembali ke beranda</a>
            <h2 class="mt-5 text-2xl font-bold text-gray-900 dark:text-white">Masuk ke Helpdesk</h2>
            <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-400">Gunakan akun yang sudah terdaftar dan terhubung dengan data NIK Anda.</p>
            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <div class="mb-1.5 flex items-center justify-between"><label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label><a href="{{ route('filament.admin.auth.password-reset.request') }}" class="text-sm font-medium text-blue-600 hover:text-indigo-600 dark:text-blue-400">Lupa password?</a></div>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 pr-12 text-sm text-gray-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        <button type="button" onclick="togglePassword()" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
                            <svg id="password-eye" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12s3.5-7 9.75-7 9.75 7 9.75 7-3.5 7-9.75 7-9.75-7-9.75-7Z"/><circle cx="12" cy="12" r="3" stroke-width="1.8"/></svg>
                            <svg id="password-eye-off" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A10.8 10.8 0 0 1 12 5c6.25 0 9.75 7 9.75 7a15.5 15.5 0 0 1-3.1 3.9M6.2 6.2C3.7 7.9 2.25 12 2.25 12s3.5 7 9.75 7c1.2 0 2.3-.25 3.3-.68"/></svg>
                        </button>
                    </div>
                    @error('password')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"><input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Ingat saya</label>
                <button class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition hover:-translate-y-0.5 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Masuk</button>
            </form>
        </section>
        <p class="mt-5 text-center text-xs font-medium text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} IT Helpdesk PTPN IV</p>
    </main>
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            document.getElementById('password-eye').classList.toggle('hidden', visible);
            document.getElementById('password-eye-off').classList.toggle('hidden', !visible);
        }
    </script>
</body>
</html>
