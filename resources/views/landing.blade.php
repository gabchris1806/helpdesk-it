<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - IT Helpdesk PTPN IV</title>
    <!-- Fonts Local / System Fallback -->
    
    <script>
        const theme = localStorage.getItem('user-theme') ?? 'system';
        if (
            theme === 'dark' ||
            (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @filamentStyles
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-[#F3F4F6] dark:bg-gray-900 transition-colors duration-300 relative overflow-x-hidden">
    
    <!-- Background Gradients -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-400/30 dark:bg-blue-600/20 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-400/30 dark:bg-indigo-600/20 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative z-10 animate-fade-in-up">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl px-4 sm:px-0 mb-6">
            <div class="flex justify-between items-start gap-4">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <img src="{{ asset('img/logo-ptpn.png') }}" alt="PTPN Logo" class="h-16 w-auto drop-shadow-md transition-transform hover:scale-105">
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            IT Helpdesk
                        </h2>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                            PTPN IV
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ auth()->check() ? (auth()->user()->isAdmin() ? url('/admin') : route('user.check-ticket')) : route('login') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-7.5a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15m-6-3h11.25m0 0l-3-3m3 3l-3 3"></path></svg>
                        {{ auth()->check() ? 'Dashboard' : 'Login' }}
                    </a>
                    <button 
                        type="button" 
                        onclick="toggleTheme()"
                        class="p-2.5 rounded-xl bg-white/80 dark:bg-gray-800/80 shadow-sm border border-gray-200/50 dark:border-gray-700/50 hover:bg-white dark:hover:bg-gray-700 transition-all focus:outline-none backdrop-blur-sm"
                        title="Ganti Tema"
                    >
                        <svg class="hidden dark:block w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg class="block dark:hidden w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <p class="mt-4 text-gray-600 dark:text-gray-400 text-lg leading-relaxed">
                Ada kendala teknis? silahkan isi form di bawah ini dan kami akan segera membantu Anda.
            </p>
        </div>

        <div class="mt-2 sm:mx-auto sm:w-full sm:max-w-2xl">
            <div class="bg-white/90 dark:bg-gray-800/90 glass-effect py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700/50 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

                <livewire:laporan-form />

            </div>
            
            <div class="mt-6 text-center">
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                    &copy; {{ date('Y') }} IT Helpdesk PTPN IV.
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('user-theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('user-theme', 'dark');
            }
        }
    </script>

    @filamentScripts
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                fail(({ status, content, preventDefault }) => {
                    if (status === 419 || status === 404) {
                        alert('Maaf, sesi Anda telah berakhir atau terjadi kesalahan koneksi. Halaman akan dimuat ulang untuk memperbarui data.');
                        window.location.reload();
                        preventDefault();
                    }
                })
            })
        });
    </script>
</body>
</html>
