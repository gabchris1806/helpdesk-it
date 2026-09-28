<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Tiket';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.chart-widget-custom';

    protected function getData(): array
    {
        $data = Ticket::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Tiket',
                    'data' => [
                        $data['Open'] ?? 0,
                        $data['Replied'] ?? 0,
                        $data['Solved'] ?? 0,
                        $data['Closed'] ?? 0,
                    ],
                    'backgroundColor' => [
                        '#fbbf24',
                        '#3b82f6',
                        '#22c55e',
                        '#ef4444',
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['Open', 'Replied', 'Solved', 'Closed'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['enabled' => true],
            ],
        ];
    }
}
