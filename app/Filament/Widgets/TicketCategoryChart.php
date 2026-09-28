<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Ticket;
use Filament\Widgets\ChartWidget;

class TicketCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Tiket per Kategori';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = null;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.chart-widget-custom';

    protected static ?string $maxHeight = '1200px';

    protected function getData(): array
    {
        $allCategories = Category::query()->pluck('name')->toArray();

        $ticketCounts = Ticket::query()
            ->selectRaw('topik_bantuan, count(*) as total')
            ->groupBy('topik_bantuan')
            ->pluck('total', 'topik_bantuan')
            ->toArray();

        $data = [];

        foreach ($allCategories as $category) {
            $data[$category] = $ticketCounts[$category] ?? 0;
        }

        arsort($data);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Tiket',
                    'data' => array_values($data),
                    'backgroundColor' => '#3b82f6',
                    'barPercentage' => 0.9,
                    'categoryPercentage' => 0.9,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'datalabels' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'y' => [
                    'display' => true,
                ],
            ],
            'layout' => [
                'padding' => [
                    'right' => 50,
                ],
            ],
        ];
    }
}
