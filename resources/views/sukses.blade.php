<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Berhasil - IT Helpdesk</title>
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .dark .glass-effect {
            background: rgba(17, 24, 39, 0.95);
        }
    </style>
</head>
<body class="bg-[#F3F4F6] dark:bg-gray-900 transition-colors duration-300 relative overflow-x-hidden min-h-screen flex items-center justify-center font-sans selection:bg-blue-500 selection:text-white">
    @php($trackingUrl = \App\Support\TicketSecurity::trackingUrl($ticket))

    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-400/30 dark:bg-blue-600/20 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-indigo-400/30 dark:bg-indigo-600/20 rounded-full blur-[100px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-lg px-4 relative z-10 animate-fade-in-up">
        <div class="glass-effect rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 p-8 sm:p-10 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

            <div class="mb-8 flex justify-center">
                <img src="{{ asset('img/logo-ptpn.png') }}" alt="PTPN Logo" class="h-16 w-auto drop-shadow-md transition-transform hover:scale-105">
            </div>

            <div class="mx-auto w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-6 animate-[bounce_1s_infinite]">
                <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2 tracking-tight">
                Laporan Diterima!
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-lg mb-8 leading-relaxed">
                Terima kasih, tim IT Support kami akan segera menindaklanjuti kendala Anda.
            </p>

            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 mb-8 border border-gray-100 dark:border-gray-700">
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Simpan Link Tiket Anda</p>
                
                <div class="relative flex items-center">
                    <input type="text" id="ticketLink" readonly
                        value="{{ $trackingUrl }}"
                        class="block w-full rounded-xl border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 text-sm font-mono py-3 pl-4 pr-12 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent cursor-pointer transition-all hover:bg-gray-50 dark:hover:bg-gray-800"
                        onclick="this.select();">
                    
                    <button onclick="copyLink()" class="absolute right-2 p-2 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900 transition-colors" title="Salin Link">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
                <div id="copyMsg" class="mt-2 text-green-600 dark:text-green-400 text-xs font-bold font-mono opacity-0 transition-opacity duration-300 flex items-center justify-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Link berhasil disalin!
                </div>
            </div>

            <div class="space-y-3">
                <a href="{{ $trackingUrl }}" 
                   class="block w-full py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/30 text-white font-bold text-sm bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Buka Halaman Tiket Sekarang
                </a>
                
                <a href="{{ route('home') }}" 
                   class="block w-full py-3.5 px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                   Kembali ke Beranda
                </a>

                @if(auth()->check() && auth()->user()->role === 'user')
                    <a href="{{ route('user.check-ticket') }}" class="block w-full py-3.5 px-4 rounded-xl border border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-300 font-bold text-sm hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">Buka Dashboard Tiket</a>
                @else
                    <a href="{{ auth()->check() && auth()->user()->isAdmin() ? url('/admin') : route('login') }}" class="block w-full py-3.5 px-4 rounded-xl border border-blue-200 dark:border-blue-900 text-blue-700 dark:text-blue-300 font-bold text-sm hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">{{ auth()->check() ? 'Buka Dashboard' : 'Login untuk melihat tiket' }}</a>
                @endif
            </div>

            <p class="mt-8 text-xs text-gray-400 dark:text-gray-500 font-medium">
                &copy; {{ date('Y') }} IT Helpdesk PTPN IV
            </p>
        </div>
    </div>

    <script>
        function copyLink() {
            var copyText = document.getElementById("ticketLink");
            copyText.select();
            copyText.setSelectionRange(0, 99999); 

            navigator.clipboard.writeText(copyText.value).then(function() {
                var msg = document.getElementById("copyMsg");
                msg.classList.remove('opacity-0');
                setTimeout(() => {
                    msg.classList.add('opacity-0');
                }, 3000);
            }, function(err) {
                alert('Gagal menyalin link. Silakan salin manual.');
            });
        }
    </script>

</body>
</html>
