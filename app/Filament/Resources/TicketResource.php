<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use App\Support\TicketSecurity;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $slug = 'laporan-ticket';

    protected static ?string $navigationLabel = 'Laporan Ticket';

    protected static ?string $modelLabel = 'Laporan';

    protected static ?string $pluralModelLabel = 'Data Laporan';

    public static function form(Form $form): Form
    {
        $halamanSaatIni = $form->getOperation();

        // === MODE 1: CREATE (Saat Admin Input Manual) ===
        if ($halamanSaatIni === 'create') {
            return $form->schema([
                Forms\Components\Group::make()->columnSpanFull()->schema([
                    Forms\Components\Section::make('Buat Tiket Laporan Baru')
                        ->schema([
                            Forms\Components\TextInput::make('nama_lengkap')->required()->label('Nama Pelapor'),
                            Forms\Components\TextInput::make('email')->email()->required(),
                            Forms\Components\TextInput::make('no_hp')->numeric()->required()->label('No WhatsApp'),

                            Forms\Components\Select::make('lokasi')
                                ->options(\App\Models\Location::pluck('name', 'name'))
                                ->required()->searchable(),

                            Forms\Components\Select::make('topik_bantuan')
                                ->label('Kategori Masalah')
                                ->options(\App\Models\Category::pluck('name', 'name'))
                                ->required()->searchable(),

                            Forms\Components\Textarea::make('deskripsi_umum_masalah')->required()->columnSpanFull()->label('Subjek'),
                            Forms\Components\RichEditor::make('penjelasan_lengkap')->columnSpanFull(),
                        ])->columns(2),
                ]),
            ]);
        }

        // === MODE 2: DETAIL (DASHBOARD ADMIN) ===
        return $form
            ->columns(3)
            ->schema([
                // === KOLOM KIRI (CHAT & DETAIL) ===
                Forms\Components\Group::make()->columnSpan(2)->schema([
                    Forms\Components\Tabs::make('Aktivitas Tiket')->tabs([
                        Forms\Components\Tabs\Tab::make('Activity')
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->extraAttributes(['wire:poll.5s' => '']) // Real-time Update (5 detik)
                            ->schema([
                                Forms\Components\Section::make()->schema([
                                    Forms\Components\TextInput::make('topik_bantuan')->disabled()->extraAttributes(['class' => 'bg-gray-100']),
                                    Forms\Components\Textarea::make('deskripsi_umum_masalah')->rows(2)->disabled()->extraAttributes(['class' => 'font-bold text-lg mb-2']),
                                    Forms\Components\RichEditor::make('penjelasan_lengkap')->disabled()->toolbarButtons([]),

                                    // TAMPILKAN GAMBAR JIKA ADA
                                    Forms\Components\Placeholder::make('gambar_display')
                                        ->hiddenLabel()
                                        ->visible(fn ($record) => $record && $record->gambar)
                                        ->content(fn ($record) => $record && $record->gambar ? new \Illuminate\Support\HtmlString(
                                            '<div class="mt-3"><p class="text-sm font-semibold text-gray-700 mb-2">Lampiran Gambar:</p><div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 12px;">'.
                                            collect($record->gambar)->map(fn ($img) => '<img src="'.asset('storage/'.$img).'" alt="Gambar Laporan" onclick="openAdminModal(this.src)" class="admin-thumbnail">')
                                                ->join('').
                                            '</div></div>'
                                        ) : '-'),
                                ])->compact(),

                                Forms\Components\Placeholder::make('separator')->content(new \Illuminate\Support\HtmlString('<div class="border-t border-gray-200 my-4"></div>')),

                                // CHAT HISTORY
                                Forms\Components\Placeholder::make('chat_history')->label('Percakapan')->content(fn ($record) => $record ? new \Illuminate\Support\HtmlString(
                                    collect($record->comments)->map(function ($c) use ($record) {
                                        $senderName = $c->user ? $c->user->name : $record->nama_lengkap;
                                        $initial = substr($senderName, 0, 1);
                                        $isAdmin = (bool) $c->user_id;
                                        $bgClass = $isAdmin ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-700'; // Green for Reporter to differentiate

                                        return '
                                    <div class="flex gap-3 mb-4">
                                        <div class="w-8 h-8 rounded-full '.$bgClass.' flex items-center justify-center text-xs font-bold flex-shrink-0">'.e($initial).'</div>
                                        <div class="flex-1 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <div class="flex justify-between items-center mb-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-bold text-sm text-gray-800">'.e($senderName).'</span>
                                                    '.(! $isAdmin ? '<span class="text-[10px] bg-gray-200 text-gray-600 px-1 rounded">Pelapor</span>' : '<span class="text-[10px] bg-blue-50 text-blue-600 px-1 rounded">Admin</span>').'
                                                </div>
                                                <span class="text-xs text-gray-500">'.e($c->created_at->diffForHumans()).'</span>
                                            </div>
                                            <div class="text-sm text-gray-700 whitespace-pre-wrap admin-trix-content">'.TicketSecurity::sanitizeRichText($c->content).'</div>
                                            '.($c->attachments ? collect(is_string($c->attachments) ? json_decode($c->attachments, true) : $c->attachments)->flatten()->filter()->map(fn ($img) => '<div class="mt-2"><img src="'.asset('storage/'.$img).'" class="admin-thumbnail" onclick="openAdminModal(this.src)"></div>')->join('') : '').'
                                        </div>
                                    </div>';
                                    })->join('')
                                ) : '-'),

                                // GLOBAL SCRIPTS & STYLES FOR ADMIN PANEL (MODAL & THUMBNAILS)
                                Forms\Components\Placeholder::make('admin_scripts')->hiddenLabel()->content(new \Illuminate\Support\HtmlString('
                                <style>
                                    /* Thumbnail Styling */
                                    .admin-thumbnail, .admin-trix-content img, .fi-fo-rich-editor img {
                                        max-height: 200px !important;
                                        width: auto !important;
                                        border-radius: 8px;
                                        border: 1px solid #ddd;
                                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                                        cursor: zoom-in;
                                        transition: transform 0.2s;
                                        display: inline-block;
                                        margin: 5px 0;
                                    }
                                    .admin-thumbnail:hover, .admin-trix-content img:hover, .fi-fo-rich-editor img:hover {
                                        opacity: 0.9;
                                        transform: scale(1.02);
                                    }
                                </style>
                                
                                <!-- Admin Image Modal -->
                                <div id="admin-modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.85);backdrop-filter:blur(4px);" onclick="closeAdminModal()">
                                    <div style="position:relative;width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                        <img id="admin-modal-image" style="max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 0 20px rgba(0,0,0,0.5);transform:scale(1);transition:transform 0.2s;" onclick="event.stopPropagation()">
                                        
                                        <!-- Close Button -->
                                        <button onclick="closeAdminModal()" style="position:absolute;top:20px;right:20px;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);color:white;width:40px;height:40px;border-radius:50%;font-size:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background 0.2s;">
                                            &times;
                                        </button>

                                        <!-- Zoom Controls -->
                                        <div style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);background:white;padding:5px;border-radius:8px;display:flex;gap:5px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                                            <button onclick="event.stopPropagation(); adminZoomOut()" style="padding:8px 12px;border:none;background:#f3f4f6;border-radius:4px;cursor:pointer;font-weight:bold;">-</button>
                                            <button onclick="event.stopPropagation(); adminResetZoom()" style="padding:8px 12px;border:none;background:#f3f4f6;border-radius:4px;cursor:pointer;font-size:12px;">Reset</button>
                                            <button onclick="event.stopPropagation(); adminZoomIn()" style="padding:8px 12px;border:none;background:#f3f4f6;border-radius:4px;cursor:pointer;font-weight:bold;">+</button>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    let adminZoomLevel = 1;
                                    
                                    function openAdminModal(src) {
                                        document.getElementById("admin-modal").style.display = "block";
                                        document.getElementById("admin-modal-image").src = src;
                                        adminZoomLevel = 1;
                                        updateAdminImageZoom();
                                    }
                                    
                                    function closeAdminModal() {
                                        document.getElementById("admin-modal").style.display = "none";
                                    }
                                    
                                    function adminZoomIn() {
                                        adminZoomLevel += 0.2;
                                        updateAdminImageZoom();
                                    }
                                    
                                    function adminZoomOut() {
                                        if (adminZoomLevel > 0.4) {
                                            adminZoomLevel -= 0.2;
                                            updateAdminImageZoom();
                                        }
                                    }
                                    
                                    function adminResetZoom() {
                                        adminZoomLevel = 1;
                                        updateAdminImageZoom();
                                    }
                                    
                                    function updateAdminImageZoom() {
                                        document.getElementById("admin-modal-image").style.transform = "scale(" + adminZoomLevel + ")";
                                    }
                                    
                                    document.addEventListener("keydown", function(e) {
                                        if (e.key === "Escape") {
                                            closeAdminModal();
                                        }
                                    });

                                    // Global Click Listener for Images in Trix Editor & Content
                                    document.addEventListener("click", function(e) {
                                        if (e.target.tagName === "IMG" && (e.target.closest(".admin-trix-content") || e.target.closest(".fi-fo-rich-editor"))) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            openAdminModal(e.target.src);
                                        }
                                    }, true);
                                </script>
                            ')),

                                Forms\Components\RichEditor::make('new_comment_content')
                                    ->label('Balas Pesan')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('comment-attachments')
                                    ->fileAttachmentsVisibility('public')
                                    ->dehydrated(false),
                                // FileUpload dihapus karena sudah di-handle RichEditor
                                // Forms\Components\FileUpload::make('new_comment_attachments')... (Removed)

                                // TOMBOL KIRIM
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('kirim_balasan')
                                        ->label('Post Reply')->color('primary')->icon('heroicon-m-paper-airplane')
                                        ->visible(fn ($record) => auth()->user()->can('update', $record) && ! $record->isClosed())
                                        ->action(function ($record, $get, $set) {
                                            abort_unless(auth()->user()->can('update', $record), 403);

                                            if (! $get('new_comment_content')) {
                                                return;
                                            }
                                            $content = TicketSecurity::sanitizeRichText($get('new_comment_content'));
                                            // $attachments = $get('new_comment_attachments'); // Tidak perlu lagi

                                            $record->comments()->create([
                                                'user_id' => auth()->id(),
                                                'content' => $content,
                                                'attachments' => null, // Attachments sudah embed di content HTML
                                            ]);

                                            $dataUpdate = ['last_reply_at' => now()];
                                            if (empty($record->replied_at)) {
                                                $dataUpdate['replied_at'] = now();
                                            }

                                            if (! in_array($record->status, ['Solved', 'Closed'])) {
                                                $dataUpdate['status'] = 'Replied';
                                                $dataUpdate['solved_at'] = null; // Reset solved jika dibalas
                                                $set('status', 'Replied');
                                            }
                                            $record->update($dataUpdate);
                                            $record->update($dataUpdate);
                                            $set('new_comment_content', '');
                                            // $set('new_comment_attachments', []); // Removed

                                            // === KIRIM NOTIFIKASI KE USER ===
                                            self::sendAdminReplyNotification($record, $content);

                                            \Filament\Notifications\Notification::make()->title('Terkirim')->success()->send();
                                        }),
                                ])->alignRight(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Data Pelapor')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Forms\Components\TextInput::make('nik')->label('NIK')->disabled(),
                                Forms\Components\TextInput::make('nama_lengkap')->label('Nama Lengkap')->disabled(),
                                Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                                Forms\Components\TextInput::make('no_hp')->label('No WhatsApp')->disabled(),
                                Forms\Components\TextInput::make('lokasi')->label('Lokasi')->disabled(),
                            ])->columns(2),
                    ]),
                ]),

                // === KOLOM KANAN (SIDEBAR: SEKARANG ADA 2 DROPDOWN SLA) ===
                Forms\Components\Group::make()->columnSpan(1)->schema([
                    Forms\Components\Section::make('Kontrol SLA')->schema([

                        Forms\Components\Placeholder::make('header_custom')->content(fn ($record) => new \Illuminate\Support\HtmlString('
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-green-100 text-green-700 flex items-center justify-center font-bold text-lg">'.substr($record->nama_lengkap ?? 'A', 0, 1).'</div>
                                <div><div class="font-bold text-gray-900">'.($record->nama_lengkap ?? '-').'</div><div class="text-xs text-gray-500">Pelapor</div></div>
                            </div>')),

                        Forms\Components\Placeholder::make('separator_sla')->content(new \Illuminate\Support\HtmlString('<div class="border-t border-gray-200 my-2"></div>')),

                        // ==========================================
                        // 1. DROPDOWN KHUSUS FIRST RESPONSE
                        // ==========================================
                        Forms\Components\Select::make('sla_id')
                            ->label('SLA: First Response')
                            ->relationship('sla', 'name')
                            ->placeholder('Pilih SLA Respon')
                            ->native(false)
                            ->live()
                            ->disabled(fn ($record) => ! auth()->user()->can('changeSla', $record))
                            ->afterStateUpdated(function ($record, $state) {
                                abort_unless(auth()->user()->can('changeSla', $record), 403);

                                // Hanya update SLA ID, tanpa mengubah status
                                $record->update(['sla_id' => $state]);
                            }),

                        // TIMER FIRST RESPONSE
                        Forms\Components\Placeholder::make('first_response_timer')
                            ->hiddenLabel()
                            ->content(function ($record, $get) {
                                if ($record->replied_at) {
                                    $text = $record->created_at->diff($record->replied_at)->format('%ad %hh %im %ss');

                                    return new \Illuminate\Support\HtmlString("<div class='bg-blue-50 p-2 rounded border border-blue-200 text-center'><span class='text-blue-700 font-bold'>✓ Responded</span><div class='text-xs'>$text</div></div>");
                                }
                                // GUNAKAN $record->sla_id LANGSUNG DARI DATABASE
                                $slaId = $record->sla_id ?? $get('sla_id');
                                if (! $slaId) {
                                    return new \Illuminate\Support\HtmlString('<div class="text-xs text-gray-400 text-center">- Pilih SLA Respon -</div>');
                                }

                                // Ambil Data SLA (Hari + Jam)
                                $sla = \App\Models\Sla::find($slaId);
                                if (! $sla) {
                                    return '-';
                                }

                                $days = (int) $sla->response_days;
                                $timeParts = explode(':', $sla->response_time ?? '00:00:00');
                                $deadline = $record->created_at->copy()->addDays($days)->addHours((int) $timeParts[0])->addMinutes((int) $timeParts[1])->timestamp * 1000;

                                return new \Illuminate\Support\HtmlString("
                                    <div class='flex justify-between items-center bg-gray-50 p-2 rounded border mb-4'>
                                        <div x-data=\"{ target: $deadline, now: new Date().getTime(), text: '...', update() { this.now = new Date().getTime(); let d = this.target - this.now; if (d < 0) { this.text = 'OVERDUE'; } else { let days = Math.floor(d / 86400000); let hours = Math.floor((d % 86400000) / 3600000); let mins = Math.floor((d % 3600000) / 60000); let secs = Math.floor((d % 60000) / 1000); this.text = days + 'd ' + hours + 'h ' + mins + 'm ' + secs + 's'; } }, init() { this.update(); setInterval(() => this.update(), 1000); } }\" x-init=\"init()\">
                                            <span x-text=\"text\" class='text-sm font-bold text-red-600'></span>
                                        </div>
                                        <div class='text-xs text-gray-500 text-right'>Target<br>{$timeParts[0]}h {$timeParts[1]}m</div>
                                    </div>
                                ");
                            }),

                        Forms\Components\Placeholder::make('separator_2')->content(new \Illuminate\Support\HtmlString('<div class="border-t border-gray-200 my-2"></div>')),

                        // ==========================================
                        // 2. DROPDOWN SLA RESOLUSI (FINAL)
                        // ==========================================
                        Forms\Components\Select::make('resolution_sla_id')
                            ->label('SLA: Resolution (Final)')
                            ->relationship('sla', 'name') // Menggunakan relasi yang sama ke tabel SLAs
                            ->placeholder('Pilih SLA Resolusi')
                            ->native(false)
                            ->live()
                            ->disabled(fn ($record) => ! auth()->user()->can('changeSla', $record))
                            ->afterStateUpdated(function ($record, $state) {
                                abort_unless(auth()->user()->can('changeSla', $record), 403);

                                $record->update(['resolution_sla_id' => $state]);
                            }),

                        // TIMER RESOLUTION
                        Forms\Components\Placeholder::make('resolution_timer')
                            ->hiddenLabel()
                            ->content(function ($record, $get) {
                                if ($record->status === 'Solved' || $record->status === 'Closed') {
                                    $text = $record->created_at->diff($record->solved_at ?? $record->closed_at ?? now())->format('%ad %hh %im %ss');

                                    return new \Illuminate\Support\HtmlString("<div class='bg-green-50 p-2 rounded border border-green-200 text-center'><span class='text-green-700 font-bold'>✓ Solved</span><div class='text-xs'>$text</div></div>");
                                }

                                $slaId = $record->resolution_sla_id ?? $get('resolution_sla_id');
                                if (! $slaId) {
                                    return new \Illuminate\Support\HtmlString('<div class="text-xs text-gray-400 text-center">- Pilih SLA Resolusi -</div>');
                                }

                                $sla = \App\Models\Sla::find($slaId);
                                if (! $sla) {
                                    return '-';
                                }

                                // Gunakan response_days (karena user menggunakan field Durasi Pengerjaan yang ada)
                                $days = (int) $sla->response_days;

                                // Ambil jam dari response_time
                                $timeParts = explode(':', $sla->response_time ?? '00:00:00');

                                $deadline = $record->created_at->copy()->addDays($days)->addHours((int) $timeParts[0])->addMinutes((int) $timeParts[1])->timestamp * 1000;

                                return new \Illuminate\Support\HtmlString("
                                    <div class='flex justify-between items-center bg-gray-50 p-2 rounded border mb-4'>
                                        <div x-data=\"{ target: $deadline, now: new Date().getTime(), text: '...', update() { this.now = new Date().getTime(); let d = this.target - this.now; if (d < 0) { this.text = 'OVERDUE'; } else { let days = Math.floor(d / 86400000); let hours = Math.floor((d % 86400000) / 3600000); let mins = Math.floor((d % 3600000) / 60000); let secs = Math.floor((d % 60000) / 1000); this.text = days + 'd ' + hours + 'h ' + mins + 'm ' + secs + 's'; } }, init() { this.update(); setInterval(() => this.update(), 1000); } }\" x-init=\"init()\">
                                            <span x-text=\"text\" class='text-sm font-bold text-red-600'></span>
                                        </div>
                                        <div class='text-xs text-gray-500 text-right'>Target<br>{$days} Hari</div>
                                    </div>
                                ");
                            }),

                        Forms\Components\Placeholder::make('separator_3')->content(new \Illuminate\Support\HtmlString('<div class="border-t border-gray-200 my-4"></div>')),

                        // STATUS SELECT
                        Forms\Components\Select::make('status')
                            ->options(['Open' => 'Open', 'Replied' => 'Replied', 'Solved' => 'Solved', 'Closed' => 'Closed'])
                            ->required()->native(false)->live()
                            ->afterStateUpdated(function ($record, $state) {
                                $d = ['status' => $state];
                                if ($state === 'Solved') {
                                    $d['solved_at'] = now();
                                } elseif ($state === 'Open') {
                                    $d['solved_at'] = null;
                                    $d['closed_at'] = null;
                                    $d['reopened_at'] = now();
                                } elseif ($state === 'Closed') {
                                    $d['closed_at'] = now();
                                }
                                $record->update($d);
                            }),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_tiket')
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        'Open' => 'warning',
                        'Replied' => 'info',
                        'Solved' => 'success',
                        'Closed' => 'danger',
                        default => 'gray',
                    })
                    ->label('No Tiket'),

                Tables\Columns\TextColumn::make('nama_lengkap')->searchable(),
                Tables\Columns\TextColumn::make('topik_bantuan')->limit(20)->label('Kategori'),
                // Sla Name dihapus sesuai request

                // === SLA FIRST RESPONSE TIMER ===
                Tables\Columns\TextColumn::make('sla_timer')
                    ->label('SLA Respon')
                    ->html()
                    ->getStateUsing(function ($record) {
                        if ($record->replied_at) {
                            $repliedAt = $record->replied_at instanceof \Carbon\Carbon ? $record->replied_at : \Carbon\Carbon::parse($record->replied_at);
                            $durasi = $record->created_at->diff($repliedAt)->format('%ad %hh %im');

                            return "<div class='text-xs text-green-600 font-bold'>✓ Done<br><span class='font-normal text-gray-500'>$durasi</span></div>";
                        }

                        return '<span class="text-xs text-gray-400">-</span>';
                    }),

                // === SLA RESOLUTION TIMER (BARU) ===
                Tables\Columns\TextColumn::make('sla_resolution_timer')
                    ->label('SLA Resolution')
                    ->html()
                    ->getStateUsing(function ($record) {
                        // Jika sudah selesai (Solved/Closed)
                        if ($record->status === 'Solved' || $record->status === 'Closed') {
                            $end = $record->solved_at ?? $record->closed_at ?? now();
                            $end = $end instanceof \Carbon\Carbon ? $end : \Carbon\Carbon::parse($end);
                            $durasi = $record->created_at->diff($end)->format('%ad %hh %im');

                            return "<div class='text-xs text-green-600 font-bold'>✓ Solved<br><span class='font-normal text-gray-500'>$durasi</span></div>";
                        }

                        return '<span class="text-xs text-gray-400">-</span>';
                    }),

                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Solved' => 'success', 'Replied' => 'info', 'Open' => 'warning', 'Closed' => 'danger', default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->label('Tanggal')
                    ->form([
                        Forms\Components\Grid::make(3)->schema([
                            DatePicker::make('created_from')->label('Dari Tanggal'),
                            DatePicker::make('created_until')->label('Sampai Tanggal'),
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('export')
                                    ->label('Export')
                                    ->icon('heroicon-m-arrow-down-tray')
                                    ->color('success')
                                    ->visible(fn () => auth()->user()->can('exportAny', \App\Models\Ticket::class))
                                    ->action(function ($livewire) {
                                        abort_unless(auth()->user()->can('exportAny', \App\Models\Ticket::class), 403);

                                        return \Maatwebsite\Excel\Facades\Excel::download(
                                            new \App\Exports\TicketExport($livewire->getFilteredTableQuery()),
                                            'Tickets-'.date('Y-m-d').'.xlsx'
                                        );
                                    }),
                            ])->extraAttributes(['class' => 'mt-8']),
                        ]),
                    ])
                    ->columnSpanFull() // Agar filter ini mengambil lebar penuh jika ada kolom lain
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make()->label('Detail')->icon('heroicon-m-eye'),
                Tables\Actions\Action::make('export_row')
                    ->label('Export')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn ($record) => auth()->user()->can('export', $record))
                    ->action(function ($record, \Filament\Tables\Actions\Action $action) {
                        $record = $record ?? $action->getRecord();

                        if (! $record || ! auth()->user()->can('export', $record)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('Surat tiket tidak ditemukan')
                                ->danger()
                                ->send();

                            return;
                        }

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TicketExport(\App\Models\Ticket::where('id', $record->id)),
                            'Ticket-'.$record->no_tiket.'.xlsx'
                        );
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->visible(fn () => auth()->user()->can('exportAny', \App\Models\Ticket::class)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }

    // === FUNGSI KIRIM NOTIFIKASI KE USER (EMAIL & WHATSAPP) ===
    private static function sendAdminReplyNotification($ticket, $replyContent)
    {
        // Kirim Email
        self::sendAdminReplyEmail($ticket, $replyContent);

        // Kirim WhatsApp
        self::sendAdminReplyWhatsApp($ticket, $replyContent);
    }

    // === KIRIM EMAIL BALASAN ADMIN (VIA QUEUE) ===
    private static function sendAdminReplyEmail($ticket, $replyContent)
    {
        // Dispatch Job Email (Type: admin_reply)
        \App\Jobs\SendEmailNotification::dispatch($ticket, 'admin_reply', $replyContent);
    }

    // === KIRIM WHATSAPP BALASAN ADMIN ===
    // === KIRIM WHATSAPP BALASAN ADMIN (VIA QUEUE) ===
    private static function sendAdminReplyWhatsApp($ticket, $replyContent)
    {
        $linkTracking = TicketSecurity::trackingUrl($ticket);

        // Potong reply content agar tidak terlalu panjang di WA (max 1024 char)
        $replyText = TicketSecurity::plainTextFromRichText($replyContent);
        $shortReply = substr($replyText, 0, 200);
        if (strlen($replyText) > 200) {
            $shortReply .= '...';
        }

        $message = "BALASAN DARI ADMIN IT HELPDESK PTPN IV\n\n"
            ."Halo {$ticket->nama_lengkap},\n\n"
            ."Tiket #: {$ticket->no_tiket}\n"
            ."Kategori: {$ticket->topik_bantuan}\n\n"
            ."Balasan Admin:\n"
            .$shortReply."\n\n"
            ."Lacak dan balas di:\n"
            ."{$linkTracking}\n\n"
            .'Terima kasih atas kesabaran Anda.';

        // Dispatch ke Queue dengan pesan kustom
        \App\Jobs\SendWhatsAppNotification::dispatch($ticket, $message);
    }

    // === HELPER: FORMAT NOMOR TELEPON ===
    private static function formatPhoneNumber($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62'.substr($phone, 1);
        }
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    // === LOAD DATA SLA DARI DATABASE KE FORM ===
    public static function mutateFormDataBeforeFill(array $data): array
    {
        // Pastikan sla_id dan resolution_sla_id dimuat dari database
        return $data;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['sla', 'resolutionSla', 'comments.user']);
    }
}
