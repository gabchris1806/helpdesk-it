<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($requiresVerification ?? false) ? 'Verifikasi Akses Tiket - Helpdesk' : 'Tiket #' . $ticket->no_tiket . ' - Helpdesk' }}</title>
    
    <!-- CDN Assets -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.3s ease-out',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        const theme = localStorage.getItem('user-theme') ?? 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        
        /* Custom Trix Styling to mimick Chat Input */
        trix-toolbar {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 0 !important;
            padding: 8px 12px !important;
            background: #f9fafb;
            border-radius: 12px 12px 0 0;
            display: flex;
            gap: 8px;
            overflow-x: auto;
        }
        .dark trix-toolbar {
            background: #374151;
            border-color: #4b5563;
        }
        
        trix-toolbar .trix-button-group {
            margin-bottom: 0 !important;
            border: none !important;
        }
        trix-toolbar .trix-button {
            border: none !important;
            background: transparent !important;
            width: 28px !important;
            height: 28px !important;
        }
        trix-toolbar .trix-button.trix-active {
            background: #e5e7eb !important;
            color: #2563eb !important;
            border-radius: 6px !important;
        }
        .dark trix-toolbar .trix-button.trix-active {
            background: #4b5563 !important;
        }

        trix-editor {
            border: none !important;
            padding: 12px !important;
            min-height: 50px !important;
            max-height: 150px;
            overflow-y: auto;
            border-radius: 0 0 12px 12px;
            background: white;
        }
        .dark trix-editor {
            background: #1f2937;
            color: white;
        }
        trix-editor:focus {
            outline: none !important;
            box-shadow: none !important;
        }
        
        /* Hide unnecessary toolbar items for cleaner look */
        trix-toolbar .trix-button--icon-heading-1,
        trix-toolbar .trix-button--icon-quote,
        trix-toolbar .trix-button--icon-code {
            display: none !important;
        }

        .chat-background {
            background-color: #f0f2f5;
        }
        .dark .chat-background {
            background-color: #111827;
        }
        
        /* Make embedded images clickable and small (thumbnail) */
        .trix-content img, trix-editor img {
            cursor: zoom-in;
            border-radius: 8px;
            max-width: 100%;
            max-height: 200px; /* Limit height for thumbnail feel */
            width: auto;
            object-fit: contain;
            margin-top: 5px;
            margin-bottom: 5px;
            transition: transform 0.2s;
            border: 1px solid #e5e7eb;
        }
        .dark .trix-content img, .dark trix-editor img {
            border-color: #4b5563;
        }
        .trix-content img:hover, trix-editor img:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body class="chat-background min-h-screen flex flex-col transition-colors duration-200">
    @php($hasAccess = !($requiresVerification ?? false))

    <!-- Sticky Glassmorphism Header -->
    <div class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm transition-all duration-200">
        <div class="max-w-3xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <a href="{{ route('home') }}" class="p-2 -ml-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                
                <div class="flex flex-col">
                    <h1 class="text-base font-bold text-gray-800 dark:text-white truncate flex items-center gap-2">
                        @if($hasAccess)
                            Ticket #{{ $ticket->no_tiket }}
                            <span id="ticket-status-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide
                                {{ $ticket->status == 'Solved' ? 'bg-green-100 text-green-700' : 
                                  ($ticket->status == 'Closed' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $ticket->status }}
                            </span>
                        @else
                            Verifikasi Akses Tiket
                        @endif
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px] sm:max-w-md">{{ $hasAccess ? $ticket->topik_bantuan : 'Masukkan email yang dipakai saat membuat tiket.' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition">
                    <svg class="hidden dark:block w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <svg class="block dark:hidden w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-data="{ show: false, message: '' }"
         x-init="@if(session('success')) message = '{{ session('success') }}'; show = true; setTimeout(() => show = false, 3000); @endif"
         @show-toast.window="message = $event.detail; show = true; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="fixed top-24 left-1/2 transform -translate-x-1/2 z-[60] px-6 py-3 bg-green-500 text-white text-sm font-bold rounded-full shadow-lg flex items-center gap-2"
         style="display: none;">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span x-text="message"></span>
    </div>

    @if(!$hasAccess)
        <div class="flex-1 flex items-center justify-center px-4 pt-24 pb-8">
            <div class="w-full max-w-md rounded-3xl border border-gray-200 bg-white/90 p-6 shadow-xl backdrop-blur dark:border-gray-700 dark:bg-gray-800/90">
                <div class="mb-5 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 .552-.448 1-1 1s-1-.448-1-1 .448-1 1-1 1 .448 1 1zm0 0V8m0 12h.01M5.071 19H19a2 2 0 002-2V7a2 2 0 00-2-2h-3.586a1 1 0 01-.707-.293l-1.414-1.414A1 1 0 0012.586 3H11.414a1 1 0 00-.707.293L9.293 4.707A1 1 0 018.586 5H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Verifikasi Akses Tiket</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Untuk membuka tiket ini, masukkan email yang digunakan saat membuat laporan.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('laporan.authorize', $ticket->uuid) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-200">Email Pelapor</label>
                        <input id="email" name="email" type="email" required class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                    </div>
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        Buka Halaman Tiket
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Main Chat Area -->
    <div class="{{ $hasAccess ? '' : 'hidden' }} flex-1 overflow-y-auto px-4 py-6 pt-24 pb-48 sm:pb-52" id="chat-container-scroll">
        <div class="max-w-3xl mx-auto space-y-6">
            
            <!-- Ticket Detail Card (Collapsible style) -->
            <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div @click="open = !open" class="px-5 py-4 flex items-center justify-between cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $ticket->deskripsi_umum_masalah }}</h2>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                     {{ $ticket->lokasi }}
                                </div>
                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Oleh: {{ $ticket->nama_lengkap }}</p>
                            </div>
                        </div>
                    </div>
                     <svg x-show="open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                     <svg x-show="!open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
                
                <div x-show="open" class="px-5 pb-5 pt-0 text-sm text-gray-600 dark:text-gray-300 border-t border-gray-100 dark:border-gray-700 mt-2">
                    <div class="prose prose-sm dark:prose-invert max-w-none mt-3 trix-content">
                        {!! \\App\\Support\\TicketSecurity::sanitizeRichText($ticket->penjelasan_lengkap) !!}
                    </div>

                    @if(! empty($ticket->gambar))
                        <div class="mt-4 pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Lampiran</p>
                            <div class="flex gap-3 overflow-x-auto pb-2">
                                @foreach($ticket->gambar as $gambar)
                                    <img src="{{ asset('storage/' . $gambar) }}" onclick="openModal(this.src)" class="h-20 w-20 object-cover rounded-lg border border-gray-200 dark:border-gray-600 shadow-sm cursor-zoom-in hover:scale-105 transition-transform">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Date Divider -->
            <div class="flex justify-center">
                <span class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-bold rounded-full uppercase tracking-widest shadow-sm">
                    Riwayat Percakapan
                </span>
            </div>

            <!-- CHAT CONTAINER -->
            <div id="chat-history">
                @include('partials.chat_history')
            </div>
            
            <!-- Spacer to prevent content from being hidden behind footer -->
            <div class="h-10"></div>

        </div>
    </div>

    <!-- Floating Bottom Input Area -->
    <div class="{{ $hasAccess ? '' : 'hidden' }} fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-gray-800/90 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700 p-4 transition-all duration-300">
        <div class="max-w-3xl mx-auto">
            
            @if($errors->any())
                 <div class="mb-3 px-4 py-2 bg-red-50 text-red-600 text-xs rounded-lg border border-red-100 flex items-center gap-2 animate-bounce">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $errors->first() }}
                </div>
            @endif



            @if($isExpired)
                <div class="text-center py-2">
                     <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 text-xs font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Tiket telah ditutup permanen
                     </span>
                </div>

            @elseif($ticket->status === 'Solved')
                <div class="flex flex-col items-center gap-3">
                    <div class="text-xs text-green-600 font-medium">Masalah ini ditandai selesai. Butuh bantuan lagi?</div>
                    <form action="{{ route('laporan.reply', $ticket->uuid) }}" method="POST" class="w-full flex gap-2">
                        @csrf
                        <input type="hidden" name="token" value="{{ $accessToken }}">
                        <input type="hidden" name="isi_pesan" value="Mohon buka kembali tiket ini, masalah belum selesai.">
                         <button type="submit" class="w-full py-3 bg-white border-2 border-green-500 text-green-600 rounded-xl font-bold hover:bg-green-50 transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.28l2.032 1.133C13.294 3 20 2.479 20 2.479m0 0v5h-9.28l-2.032-1.133C10.706 7 4 7.521 4 7.521m-3 9h1m0 0v5m0-5h9.28l2.032 1.133C13.294 21 20 21.521 20 21.521M24 16h-1m0 0v5m0-5h-9.28l-2.032-1.133C10.706 14 4 13.479 4 13.479"></path></svg>
                            Buka Kembali Tiket
                        </button>
                    </form>
                </div>

            @elseif(!$adminSudahJawab)
                <div class="text-center py-2 opacity-75">
                     <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 text-xs font-semibold animate-pulse">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Menunggu respon awal admin...
                     </span>
                </div>

            @else
                <form id="reply-form" action="{{ route('laporan.reply', $ticket->uuid) }}" method="POST" enctype="multipart/form-data" class="relative">
                    @csrf
                    <input type="hidden" name="token" value="{{ $accessToken }}">
                    
                    <div class="bg-gray-100 dark:bg-gray-700/50 rounded-2xl p-1 border border-transparent focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all shadow-inner">
                        <input id="reply-input" type="hidden" name="isi_pesan">
                        <trix-editor input="reply-input" placeholder="Tulis pesan balasan..." class="bg-transparent border-none min-h-[50px] focus:ring-0 px-3 py-2 text-sm md:text-base"></trix-editor>
                        
                        <div class="flex items-center justify-between px-2 pb-1 pt-1 border-t border-gray-200 dark:border-gray-600 mt-1">
                            <div class="flex items-center gap-2">
                            </div>
                            <button type="submit" id="submit-btn" class="p-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow-md flex items-center gap-2 px-4 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="text-xs font-bold">Kirim</span>
                                <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </button>
                        </div>
                    </div>
                </form>

                <script>
                    (function() {
                        const ticketUuid = @json($ticket->uuid);
                        const ticketAccessToken = @json($accessToken);
                        const uploadUrl = @json(route('laporan.upload_trix'));
                        addEventListener("trix-attachment-add", function(event) {
                            if (event.attachment.file) { uploadFileAttachment(event.attachment) }
                        })
                        function uploadFileAttachment(attachment) { uploadFile(attachment.file, setProgress, setAttributes)
                            function setProgress(progress) { attachment.setUploadProgress(progress) }
                            function setAttributes(attributes) { attachment.setAttributes(attributes) }
                        }
                        function uploadFile(file, progressCallback, successCallback) {
                            var formData = new FormData(); var xhr = new XMLHttpRequest();
                            formData.append("file", file); formData.append("uuid", ticketUuid); formData.append("token", ticketAccessToken); formData.append("_token", "{{ csrf_token() }}");
                            xhr.open("POST", uploadUrl, true);
                            xhr.upload.addEventListener("progress", function(event) { progressCallback(event.loaded / event.total * 100) });
                            xhr.addEventListener("load", function(event) {
                                if (xhr.status == 200) { var response = JSON.parse(xhr.responseText); successCallback({ url: response.url, href: response.url }) }
                                else { alert('Upload gambar ditolak. Pastikan file berupa gambar maksimal 5MB.'); }
                            });
                            xhr.send(formData);
                        }

                        // === AJAX FORM SUBMISSION ===
                        const form = document.getElementById('reply-form');
                        const submitBtn = document.getElementById('submit-btn');
                        
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const content = document.getElementById('reply-input').value;
                            if(!content.trim()) return;

                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

                            const formData = new FormData(form);

                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if(data.success) {
                                    // 1. Append new message
                                    const chatHistory = document.getElementById('chat-history');
                                    // Remove "empty state" if exists
                                    const emptyState = chatHistory.querySelector('.opacity-60');
                                    if(emptyState) emptyState.remove();

                                    chatHistory.insertAdjacentHTML('beforeend', data.html);

                                    // 2. Clear input
                                    document.querySelector("trix-editor").editor.loadHTML("");
                                    document.getElementById('reply-input').value = "";

                                    // 3. Scroll to bottom
                                    scrollToBottom();

                                    // 4. Show Toast (Using the Alpine component we added)
                                    // Dispatch event to show toast
                                    window.dispatchEvent(new CustomEvent('show-toast', { detail: data.message }));
                                } else {
                                    alert('Gagal mengirim pesan');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan. Silakan coba lagi.');
                            })
                            .finally(() => {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<span class="text-xs font-bold">Kirim</span><svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>';
                            });
                        });
                    })();
                </script>
            @endif

        </div>
    </div>

    <!-- Modal Zoom -->
    <div id="imageModal" class="hidden fixed inset-0 bg-black/95 z-[60] flex items-center justify-center p-4 backdrop-blur-sm" onclick="if(event.target === this) closeModal()">
        <button onclick="closeModal()" class="absolute top-6 right-6 z-[70] p-2 bg-white/10 hover:bg-white/20 text-white rounded-full transition backdrop-blur-md shadow-lg border border-white/10 group">
            <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <img id="modalImage" src="" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl transition-transform duration-300">
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function openModal(src) { document.getElementById('imageModal').classList.remove('hidden'); document.getElementById('modalImage').src = src; }
        function closeModal() { document.getElementById('imageModal').classList.add('hidden'); }
        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) { html.classList.remove('dark'); localStorage.setItem('user-theme', 'light'); } 
            else { html.classList.add('dark'); localStorage.setItem('user-theme', 'dark'); }
        }
        
        const scrollContainer = document.getElementById('chat-container-scroll');
        const accessToken = @json($accessToken ?? null);

        function scrollToBottom() { 
            if(scrollContainer) {
                // Gunakan scrollTop max untuk memastikan scroll sampai paling bawah (termasuk padding)
                scrollContainer.scrollTop = scrollContainer.scrollHeight;
            }
        }
        
        // Execute immediately and after small delays to account for rendering/images
        window.addEventListener('load', function() {
            scrollToBottom();
            setTimeout(scrollToBottom, 100);
            setTimeout(scrollToBottom, 500);
        });
        
        // Also run now in case load already happened
        scrollToBottom();
        setTimeout(scrollToBottom, 300);

        // === GLOBAL IMAGE CLICK HANDLER FOR TRIX CONTENT ===
        document.addEventListener('click', function(e) {
            // Check if clicked element is an image inside .trix-content OR trix-editor
            if (e.target.tagName === 'IMG' && (e.target.closest('.trix-content') || e.target.closest('trix-editor'))) {
                // Prevent default behavior (e.g. if wrapped in an anchor tag)
                e.preventDefault();
                e.stopPropagation();
                
                // Open modal
                openModal(e.target.src);
            }
        }, true); // Use capture phase to ensure we catch it before other listeners if needed

        // === REAL-TIME UDPATES (POLLING) ===
        // Poll every 10 seconds to check for new messages
        // NOW: PURELY SILENT - No visual shaking guaranteed
        
        if (accessToken) setInterval(function() {
            fetch('{{ route("laporan.chat-history") }}?uuid={{ $ticket->uuid }}&token={{ $accessToken ?? '' }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                // 1. Check Status Change
                const statusBadge = document.getElementById('ticket-status-badge');
                if (statusBadge && data.status !== statusBadge.innerText.trim()) {
                    if (data.status === 'Solved' || data.status === 'Closed') {
                        window.location.reload();
                        return;
                    }
                    statusBadge.innerText = data.status;
                    
                    // Update class color
                    statusBadge.className = "px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide " + 
                        (data.status == 'Solved' ? 'bg-green-100 text-green-700' : 
                        (data.status == 'Closed' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'));
                }

                // 2. Check Admin Reply
                @if(!$adminSudahJawab)
                    if (data.adminSudahJawab) {
                        window.location.reload();
                        return;
                    }
                @endif

                // 3. Update Chat History (SMOOTH NO-FLICKER)
                if (data.html) {
                    const chatHistory = document.getElementById('chat-history');
                    
                     // Helper to strip whitespace text nodes for cleaner comparison
                    const cleanHTML = (html) => html.replace(/>\s+</g, '><').trim();

                     // Create a virtual DOM to compare
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    const oldChildren = Array.from(chatHistory.children);
                    const newChildren = Array.from(tempDiv.children);

                     // Check for "Empty State" removal
                     // If we currently have the "Empty State" (opacity-60) and new data has real messages
                    const hasEmptyState = chatHistory.querySelector('.opacity-60');
                    if (hasEmptyState && newChildren.length > 0 && !tempDiv.querySelector('.opacity-60')) {
                        chatHistory.innerHTML = data.html;
                        scrollToBottom();
                        return;
                    }

                     // If new data has MORE children than current, append the difference
                    if (newChildren.length > oldChildren.length) {
                        const isAtBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop <= scrollContainer.clientHeight + 150;

                         // Append only the new nodes
                        for (let i = oldChildren.length; i < newChildren.length; i++) {
                            const newNode = newChildren[i].cloneNode(true);
                             // Add slide-up animation if using tailwind
                            newNode.classList.add('animate-fade-in-up'); 
                            chatHistory.appendChild(newNode);
                        }

                        if (isAtBottom) scrollToBottom();
                    } 
                     // Fallback: If somehow length is same but content changed (edited?), or length is smaller (deleted?)
                     // Just replace content ONLY if strict mismatch (rare case in this system)
                    else if (cleanHTML(chatHistory.innerHTML) !== cleanHTML(data.html)) {
                         // Only replace if it's NOT just a new message appended (e.g. edit)
                         // But for now, let's stick to append-only for smoothness unless lengths mismatch weirdly.
                         // If we are strictly just waiting for new messages, the length check covers 99% cases.
                    }
                }
            })
            .catch(err => console.error('Silent poll error:', err));
        }, 3000); // Check every 3 seconds for faster response 

    </script>
</body>
</html>








