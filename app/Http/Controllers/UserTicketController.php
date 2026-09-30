<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserTicketController extends Controller
{
    public function index(Request $request): View
    {
        $nik = $request->user()->masterLapor->nik;
        $tickets = Ticket::query()->where('nik', $nik)->latest()->paginate(15);

        return view('user.check-ticket', compact('tickets'));
    }
}
