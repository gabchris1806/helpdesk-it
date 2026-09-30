<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ticket extends Model
{
    // Izinkan semua kolom diisi
    // Izinkan semua kolom diisi
    protected $guarded = [];

    // Pastikan kolom ini dianggap sebagai tanggal oleh Laravel
    protected $casts = [
        'sla_due_at' => 'datetime',
        'resolution_due_at' => 'datetime',
        'replied_at' => 'datetime',
        'solved_at' => 'datetime',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    protected function gambar(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                $decoded = is_string($value) ? json_decode($value, true) : $value;

                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }

                if (! is_array($decoded)) {
                    return [];
                }

                return collect($decoded)
                    ->flatten()
                    ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
                    ->values()
                    ->all();
            },
            set: function (mixed $value): ?string {
                $decoded = is_string($value) ? json_decode($value, true) : $value;

                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }

                if (! is_array($decoded)) {
                    return null;
                }

                $paths = collect($decoded)
                    ->flatten()
                    ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
                    ->values()
                    ->all();

                // Attribute mutators must return a scalar here. Returning the
                // paths array makes Eloquent interpret each numeric key as a
                // separate database column (for example column `0`).
                return $paths !== [] ? json_encode($paths) : null;
            },
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->no_tiket)) {
                $model->no_tiket = 'TICKET-' . strtoupper(Str::random(5));
            }

            // === 1. LOGIKA FIRST RESPONSE ===
            if (empty($model->sla_id)) {
                // Cari SLA dengan nama 'first response' (case-insensitive)
                $firstResponseSla = Sla::where('name', 'LIKE', 'first response')->first();
                if ($firstResponseSla) {
                    // A. Tempel ID
                    $model->sla_id = $firstResponseSla->id;

                    // B. HITUNG DEADLINE (Disini Rumusnya!)
                    $time = Carbon::parse($firstResponseSla->response_time);
                    
                    $model->sla_due_at = now()
                        ->addDays((int) $firstResponseSla->response_days) // Tambah Hari
                        ->addHours($time->hour)      // Tambah Jam
                        ->addMinutes($time->minute); // Tambah Menit
                }
            }

            // === 2. LOGIKA RESOLUTION (Yang Kakak Cari) ===
            if (empty($model->resolution_sla_id)) {
                // Cari SLA dengan nama 'resolution' (case-insensitive)
                $resolutionSla = Sla::where('name', 'LIKE', 'resolution')->first();
                if ($resolutionSla) {
                    // A. Tempel ID
                    $model->resolution_sla_id = $resolutionSla->id;

                    // B. HITUNG DEADLINE (Rumus diperbaiki)
                    // Ambil jam & menit dari SLA
                    $timeRes = Carbon::parse($resolutionSla->response_time);
                    
                    $model->resolution_due_at = now()
                        ->addDays((int) $resolutionSla->response_days)
                        ->addHours($timeRes->hour)
                        ->addMinutes($timeRes->minute);
                }
            }
        });
    }

    // === RELASI ===

    // 1. Relasi SLA untuk FIRST RESPONSE (Default)
    public function sla()
    {
        return $this->belongsTo(Sla::class, 'sla_id');
    }

    // 2. Relasi SLA untuk RESOLUTION
    public function resolutionSla()
    {
        return $this->belongsTo(Sla::class, 'resolution_sla_id');
    }

    // 3. Relasi Komentar
    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    // === HELPER ===
    public function isClosed()
    {
        return $this->status === 'Closed';
    }
}
