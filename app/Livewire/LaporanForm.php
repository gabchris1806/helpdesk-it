<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Location;
use App\Models\Ticket;
use App\Support\TicketSecurity;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class LaporanForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pelapor')
                    ->description('NIK digunakan untuk verifikasi. Data kontak diisi manual untuk mencegah kebocoran data internal.')
                    ->schema([
                        Forms\Components\TextInput::make('nik')
                            ->label('NIK')
                            ->required()
                            ->exists('master_lapors', 'nik')
                            ->validationMessages([
                                'exists' => 'Data tidak dapat diverifikasi. Silakan periksa kembali NIK Anda.',
                            ]),

                        Forms\Components\TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('no_hp')
                            ->label('No WhatsApp')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->placeholder('08...'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('lokasi')
                            ->label('Lokasi / Unit Kerja')
                            ->options(Location::query()->pluck('name', 'name'))
                            ->searchable()
                            ->required(),
                    ])->columns(['default' => 1, 'sm' => 2]),

                Forms\Components\Section::make('Detail Masalah')
                    ->schema([
                        Forms\Components\Select::make('topik_bantuan')
                            ->label('Kategori Masalah')
                            ->options(Category::query()->pluck('name', 'name'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('deskripsi_umum_masalah')
                            ->label('Judul Laporan')
                            ->placeholder('Contoh: Printer Macet di Ruang Keuangan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('penjelasan_lengkap')
                            ->label('Detail Kronologi')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('gambar')
                            ->label('Foto Langsung / Upload Bukti (Opsional)')
                            ->multiple()
                            ->image()
                            ->directory('laporan-gambar')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create()
    {
        $key = 'kirim-tiket:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('rate_limit', "Mohon tunggu {$seconds} detik lagi sebelum mengirim laporan baru.");
            return null;
        }

        RateLimiter::hit($key, 60);

        $data = $this->form->getState();
        $data['nama_lengkap'] = TicketSecurity::sanitizePlainText($data['nama_lengkap'] ?? '');
        $data['email'] = strtolower(trim((string) ($data['email'] ?? '')));
        $data['no_hp'] = preg_replace('/\D+/', '', (string) ($data['no_hp'] ?? '')) ?: '';
        $data['lokasi'] = TicketSecurity::sanitizePlainText($data['lokasi'] ?? '');
        $data['topik_bantuan'] = TicketSecurity::sanitizePlainText($data['topik_bantuan'] ?? '');
        $data['deskripsi_umum_masalah'] = TicketSecurity::sanitizePlainText($data['deskripsi_umum_masalah'] ?? '');
        $data['penjelasan_lengkap'] = TicketSecurity::sanitizeRichText($data['penjelasan_lengkap'] ?? '');

        if (isset($data['gambar']) && is_array($data['gambar'])) {
            $data['gambar'] = array_values($data['gambar']);
        }

        $ticket = Ticket::create($data);

        \App\Jobs\SendWhatsAppNotification::dispatch($ticket);

        return redirect()->route('laporan.sukses', ['uuid' => $ticket->uuid]);
    }

    public function render()
    {
        return view('livewire.laporan-form');
    }
}
