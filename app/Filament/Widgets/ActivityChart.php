<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use App\Models\DocumentShare;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ActivityChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Activity';

    protected ?string $description = 'Documents created and shares granted per day.';

    protected ?string $maxHeight = '260px';

    protected ?string $pollingInterval = null;

    public ?string $filter = '14';

    /**
     * @return array<string, string>
     */
    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '14' => 'Last 14 days',
            '30' => 'Last 30 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 14);
        $start = now()->subDays($days - 1)->startOfDay();
        $period = collect(range(0, $days - 1))->map(fn (int $offset): CarbonInterface => $start->copy()->addDays($offset));

        return [
            'datasets' => [
                [
                    'label' => 'Documents created',
                    'data' => $this->series(Document::class, $start, $period),
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Shares granted',
                    'data' => $this->series(DocumentShare::class, $start, $period),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $period->map(fn (CarbonInterface $day): string => $day->format('M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }

    /**
     * @param  class-string<Model>  $model
     * @param  Collection<int, CarbonInterface>  $period
     * @return array<int, int>
     */
    private function series(string $model, CarbonInterface $start, Collection $period): array
    {
        $counts = $model::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $period->map(fn (CarbonInterface $day): int => (int) ($counts[$day->toDateString()] ?? 0))->all();
    }
}
