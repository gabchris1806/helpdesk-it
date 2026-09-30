<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - IT Helpdesk PTPN IV</title>
    <script>
        const theme = localStorage.getItem('user-theme') ?? 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
</head>
<body class="min-h-screen bg-[#F3F4F6] font-sans text-gray-800 transition-colors dark:bg-gray-900 dark:text-gray-100">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-[10%] -top-[10%] h-[35%] w-[35%] rounded-full bg-blue-400/20 blur-[100px] dark:bg-blue-600/10"></div>
        <div class="absolute -bottom-[10%] -right-[10%] h-[35%] w-[35%] rounded-full bg-indigo-400/20 blur-[100px] dark:bg-indigo-600/10"></div>
    </div>
    <header class="relative z-10 border-b border-gray-200/70 bg-white/80 shadow-sm backdrop-blur-md dark:border-gray-700/60 dark:bg-gray-900/80">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/logo-ptpn.png') }}" alt="Logo PTPN" class="h-11 w-auto">
                <div><p class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">IT Helpdesk PTPN IV</p><h1 class="text-lg font-bold">Tiket Saya</h1><p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->name }} <span class="px-1">·</span> NIK {{ auth()->user()->masterLapor->nik }}</p></div>
            </div>
            <nav class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10.5 12 3l9 7.5M5.25 9v11.25h13.5V9M9 20.25v-6h6v6"/></svg>
                    <span class="hidden sm:inline">Halaman utama</span>
                </a>
                <button type="button" onclick="toggleTheme()" aria-label="Ganti tema" class="rounded-xl border border-gray-200 bg-white/80 p-2.5 text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-yellow-400 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.36 6.36-1.42-1.42M7.05 7.05 5.64 5.64m12.72 0-1.41 1.41M7.05 16.95l-1.41 1.41M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.35 15.35A9 9 0 0 1 8.65 3.65 9 9 0 1 0 20.35 15.35Z"/></svg>
                </button>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:from-blue-700 hover:to-indigo-700"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m-6-3h11.25m0 0-3-3m3 3-3 3"/></svg><span>Keluar</span></button></form>
            </nav>
        </div>
    </header>

    <main class="relative z-10 mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div><h2 class="text-2xl font-bold tracking-tight">Riwayat laporan</h2><p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lihat status, percakapan, dan perkembangan laporan Anda.</p></div>
            <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-sm font-semibold text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/60 dark:text-blue-300">{{ $tickets->total() }} tiket</span>
        </div>
        <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white/90 shadow-xl dark:border-gray-700/60 dark:bg-gray-800/90">
            <div class="h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>
            @if($tickets->isEmpty())
                <div class="px-6 py-16 text-center"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-300"><svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></div><h3 class="mt-4 font-semibold">Belum ada tiket</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Laporan yang dibuat menggunakan NIK Anda akan muncul di sini.</p><a href="{{ route('home') }}" class="mt-5 inline-flex rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">Buat laporan</a></div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-sm">
                        <thead class="bg-gray-50/80 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-900/50 dark:text-gray-400"><tr><th class="px-5 py-4 font-semibold">No. Tiket</th><th class="px-5 py-4 font-semibold">Topik</th><th class="px-5 py-4 font-semibold">Lokasi</th><th class="px-5 py-4 font-semibold">Status</th><th class="px-5 py-4 font-semibold">Tanggal</th><th class="px-5 py-4"></th></tr></thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                        @foreach($tickets as $ticket)
                            @php($statusStyle = match (strtolower($ticket->status)) { 'solved' => 'bg-green-50 text-green-700 dark:bg-green-950/50 dark:text-green-300', 'closed' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'replied' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300', default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' })
                            <tr class="transition hover:bg-blue-50/40 dark:hover:bg-gray-700/30"><td class="whitespace-nowrap px-5 py-4 font-semibold text-gray-900 dark:text-white">{{ $ticket->no_tiket }}</td><td class="max-w-xs px-5 py-4"><span class="block truncate font-medium">{{ $ticket->topik_bantuan }}</span></td><td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $ticket->lokasi }}</td><td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusStyle }}">{{ $ticket->status }}</span></td><td class="whitespace-nowrap px-5 py-4 text-gray-600 dark:text-gray-300">{{ $ticket->created_at?->format('d M Y') }}</td><td class="whitespace-nowrap px-5 py-4"><a class="inline-flex items-center gap-1.5 font-semibold text-blue-700 hover:text-indigo-700 hover:underline dark:text-blue-300 dark:hover:text-indigo-300" href="{{ route('laporan.cek', ['uuid' => $ticket->uuid]) }}">Detail & chat <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg></a></td></tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if($tickets->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-700">{{ $tickets->links() }}</div>@endif
            @endif
        </section>
    </main>
    <script>
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('user-theme', isDark ? 'dark' : 'light');
        }
    </script>
</body>
</html>
