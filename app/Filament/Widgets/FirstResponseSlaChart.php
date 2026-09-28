<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class FirstResponseSlaChart extends ChartWidget
{
    protected static ?string $heading = 'SLA First Response';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected static string $view = 'filament.widgets.chart-widget-custom';

    protected function getData(): array
    {
        $tickets = Ticket::query()
            ->select(['id', 'sla_id', 'created_at', 'replied_at'])
            ->whereNotNull('sla_id')
            ->with(['sla:id,response_days,response_time'])
            ->get();

        $onTime = 0;
        $overdue = 0;
        $running = 0;
        $now = now();

        foreach ($tickets as $ticket) {
            $sla = $ticket->sla;

            if (! $sla) {
                continue;
            }

            $timeParts = explode(':', $sla->response_time ?? '00:00:00');
            $deadline = Carbon::parse($ticket->created_at)
                ->addDays((int) $sla->response_days)
                ->addHours((int) ($timeParts[0] ?? 0))
                ->addMinutes((int) ($timeParts[1] ?? 0));

            if ($ticket->replied_at) {
                if (Carbon::parse($ticket->replied_at)->lte($deadline)) {
                    $onTime++;
                } else {
                    $overdue++;
                }

                continue;
            }

            if ($now->gt($deadline)) {
                $overdue++;
            } else {
                $running++;
            }
        }

        $total = $onTime + $overdue + $running;
        $formatLabel = fn (string $label, int $value): string => $label . ' (' . ($total > 0 ? round(($value / $total) * 100, 1) : 0) . '% - ' . $value . ')';

        return [
            'datasets' => [
                [
                    'label' => 'SLA Kinerja',
                    'data' => [$onTime, $overdue, $running],
                    'backgroundColor' => ['#22c55e', '#ef4444', '#94a3b8'],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => [
                $formatLabel('On Time', $onTime),
                $formatLabel('Overdue', $overdue),
                $formatLabel('Dalam Proses', $running),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
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
