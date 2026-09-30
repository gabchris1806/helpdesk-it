<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Category;
use App\Models\Location;
use App\Models\Ticket;
use App\Support\TicketSecurity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PublicTicketController extends Controller
{
    public function index(): View
    {
        $locations = Location::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('landing', compact('locations', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $key = 'kirim-tiket:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withInput()
                ->withErrors(['limit' => "Mohon tunggu {$seconds} detik lagi sebelum mengirim laporan baru."]);
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_hp' => 'required|string|max:20',
            'lokasi' => 'required|string|max:255',
            'topik_bantuan' => 'required|string|max:255',
            'deskripsi_umum_masalah' => 'required|string|max:255',
            'penjelasan_lengkap' => 'required|string',
            'gambar.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        RateLimiter::hit($key, 60);

        $validated = $this->sanitizeTicketPayload($validated);
        $validated['gambar'] = $this->storeUploadedImages($request, 'gambar', 'laporan-gambar');

        $ticket = Ticket::create($validated);

        $this->sendEmailNotification($ticket);
        $this->sendWhatsAppNotification($ticket);

        return redirect()->route('laporan.sukses', ['uuid' => $ticket->uuid]);
    }

    public function success(string $uuid): View
    {
        $ticket = $this->findTicketByUuid($uuid);

        return view('sukses', compact('ticket'));
    }

    public function cek(Request $request): View|RedirectResponse
    {
        $uuid = (string) $request->query('uuid', '');

        if ($uuid === '') {
            return redirect()->route('home');
        }

        $ticket = $this->findTicketByUuid($uuid);
        $hasAccess = $this->hasTicketAccess($request, $ticket);

        return view('lacak', [
            'ticket' => $ticket,
            'isExpired' => $hasAccess ? $this->isTicketExpired($ticket) : false,
            'adminSudahJawab' => $hasAccess ? $ticket->comments()->whereNotNull('user_id')->exists() : false,
            'requiresVerification' => ! $hasAccess,
            'accessToken' => $hasAccess ? TicketSecurity::generateAccessToken($ticket) : null,
        ]);
    }

    public function authorizeAccess(Request $request, string $uuid): RedirectResponse
    {
        $ticket = $this->findTicketByUuid($uuid);

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        if (strcasecmp(trim($validated['email']), (string) $ticket->email) !== 0) {
            return redirect()
                ->route('laporan.cek', ['uuid' => $ticket->uuid])
                ->withErrors(['email' => 'Email tidak cocok dengan tiket ini.']);
        }

        return redirect()->route('laporan.cek', [
            'uuid' => $ticket->uuid,
            'token' => TicketSecurity::generateAccessToken($ticket),
        ]);
    }

    public function reply(Request $request, string $uuid): JsonResponse|RedirectResponse
    {
        $ticket = $this->findTicketByUuid($uuid);

        if (! $this->hasTicketAccess($request, $ticket)) {
            return $this->forbiddenResponse($request, 'Akses tiket tidak valid atau sudah kedaluwarsa.');
        }

        $request->validate([
            'isi_pesan' => 'required|string',
            'token' => 'required|string',
            'attachments.*' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        if ($this->isTicketExpired($ticket)) {
            return $this->validationResponse($request, 'Tiket ini sudah ditutup permanen dan tidak bisa dibalas lagi.');
        }

        if (! $ticket->comments()->whereNotNull('user_id')->exists()) {
            return $this->validationResponse($request, 'Mohon tunggu balasan dari Admin terlebih dahulu sebelum mengirim pesan.');
        }

        $attachmentPaths = $this->storeUploadedImages($request, 'attachments', 'comment-attachments');
        $content = TicketSecurity::sanitizeRichText($request->input('isi_pesan'));

        $ticket->comments()->create([
            'user_id' => null,
            'content' => $content,
            'attachments' => $attachmentPaths,
        ]);

        if ($ticket->status !== 'Open') {
            $ticket->update(['status' => 'Open', 'reopened_at' => now(), 'solved_at' => null]);
        } else {
            $ticket->update(['reopened_at' => now()]);
        }

        if ($request->expectsJson()) {
            $html = view('partials.chat_single', [
                'comment' => $ticket->comments()->latest()->first(),
                'ticket' => $ticket,
            ])->render();

            return response()->json([
                'success' => true,
                'message' => 'Pesan terkirim!',
                'html' => $html,
            ]);
        }

        return back()->with('success', 'Pesan terkirim!');
    }

    public function chatHistory(Request $request): JsonResponse
    {
        $uuid = (string) $request->query('uuid', '');

        if ($uuid === '') {
            return response()->json(['error' => 'UUID required'], 400);
        }

        $ticket = $this->findTicketByUuid($uuid);

        if (! $this->hasTicketAccess($request, $ticket)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $html = view('partials.chat_history', compact('ticket'))->render();

        return response()->json([
            'html' => $html,
            'status' => $ticket->status,
            'adminSudahJawab' => $ticket->comments()->whereNotNull('user_id')->exists(),
            'isExpired' => $this->isTicketExpired($ticket),
        ]);
    }

    public function uploadTrixImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
            'token' => 'required|string',
            'file' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $ticket = $this->findTicketByUuid($validated['uuid']);

        if (! TicketSecurity::hasValidAccessToken($ticket, $validated['token'])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $path = $request->file('file')->store('trix-attachments', 'public');

        return response()->json(['url' => asset('storage/'.$path)]);
    }

    private function sendEmailNotification(Ticket $ticket): void
    {
        SendEmailNotification::dispatchSync($ticket);
    }

    private function sendWhatsAppNotification(Ticket $ticket): void
    {
        SendWhatsAppNotification::dispatch($ticket);
    }

    private function isTicketExpired(Ticket $ticket): bool
    {
        $days = 5;

        if ($ticket->resolutionSla) {
            $days = (int) $ticket->resolutionSla->response_days;
        } elseif ($ticket->sla) {
            $days = (int) $ticket->sla->response_days;
        }

        return $ticket->created_at->copy()->addDays($days)->isPast() || $ticket->status === 'Closed';
    }

    private function sanitizeTicketPayload(array $validated): array
    {
        $validated['nama_lengkap'] = TicketSecurity::sanitizePlainText($validated['nama_lengkap']);
        $validated['email'] = strtolower(trim($validated['email']));
        $validated['no_hp'] = preg_replace('/\D+/', '', $validated['no_hp']) ?: '';
        $validated['lokasi'] = TicketSecurity::sanitizePlainText($validated['lokasi']);
        $validated['topik_bantuan'] = TicketSecurity::sanitizePlainText($validated['topik_bantuan']);
        $validated['deskripsi_umum_masalah'] = TicketSecurity::sanitizePlainText($validated['deskripsi_umum_masalah']);
        $validated['penjelasan_lengkap'] = TicketSecurity::sanitizeRichText($validated['penjelasan_lengkap']);

        return $validated;
    }

    private function storeUploadedImages(Request $request, string $field, string $directory): ?array
    {
        $paths = [];

        if (! $request->hasFile($field)) {
            return null;
        }

        foreach ((array) $request->file($field) as $file) {
            if ($file && $file->isValid()) {
                $paths[] = $file->store($directory, 'public');
            }
        }

        return ! empty($paths) ? array_values($paths) : null;
    }

    private function findTicketByUuid(string $uuid): Ticket
    {
        return Ticket::where('uuid', $uuid)->firstOrFail();
    }

    private function hasTicketAccess(Request $request, Ticket $ticket): bool
    {
        $user = $request->user();
        if ($user && $user->masterLapor && hash_equals((string) $ticket->nik, (string) $user->masterLapor->nik)) {
            return true;
        }

        $token = $request->query('token', $request->input('token'));

        return TicketSecurity::hasValidAccessToken($ticket, is_string($token) ? $token : null);
    }

    private function validationResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        return back()->withErrors(['status' => $message]);
    }

    private function forbiddenResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()->route('home')->withErrors(['status' => $message]);
    }
}
