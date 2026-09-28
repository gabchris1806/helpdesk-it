<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $statusCounts = Ticket::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $openTicket = (int) ($statusCounts['Open'] ?? 0);
        $repliedTicket = (int) ($statusCounts['Replied'] ?? 0);
        $solvedTicket = (int) ($statusCounts['Solved'] ?? 0);
        $closedTicket = (int) ($statusCounts['Closed'] ?? 0);
        $totalTicket = $openTicket + $repliedTicket + $solvedTicket + $closedTicket;

        return [
            Stat::make('Total Ticket', $totalTicket)
                ->description('Semua tiket yang masuk')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Open', $openTicket)
                ->description('Tiket aktif/pesan dari user')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Replied', $repliedTicket)
                ->description('Admin sudah merespon')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            Stat::make('Solved', $solvedTicket)
                ->description('Masalah terselesaikan')
                ->descriptionIcon('heroicon-m-check')
                ->color('success'),
            Stat::make('Closed', $closedTicket)
                ->description('Tiket ditutup')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
