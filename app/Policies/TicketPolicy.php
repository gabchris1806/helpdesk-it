<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('ticket.view');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('ticket.create');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.update');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.delete');
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.delete');
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.delete');
    }

    public function changeSla(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.change_sla');
    }

    public function exportAny(User $user): bool
    {
        return $user->hasPermission('ticket.export');
    }

    public function export(User $user, Ticket $ticket): bool
    {
        return $user->hasPermission('ticket.export');
    }
}
