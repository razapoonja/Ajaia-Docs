<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class TopOwnersChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Top owners';

    protected ?string $description = 'Who owns the most documents, and how many of them are shared.';

    protected ?string $maxHeight = '260px';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $owners = User::query()
            ->withCount([
                'ownedDocuments',
                'ownedDocuments as shared_documents_count' => fn ($query) => $query->has('shares'),
            ])
            ->has('ownedDocuments')
            ->orderByDesc('owned_documents_count')
            ->limit(8)
            ->get(['id', 'name']);

        return [
            'datasets' => [
                [
                    'label' => 'Shared',
                    'data' => $owners->pluck('shared_documents_count')->all(),
                    'backgroundColor' => '#2563eb',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Private',
                    'data' => $owners->map(fn (User $owner): int => $owner->owned_documents_count - $owner->shared_documents_count)->all(),
                    'backgroundColor' => '#bfdbfe',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $owners->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'x' => ['stacked' => true, 'grid' => ['display' => false]],
                'y' => ['stacked' => true, 'beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
