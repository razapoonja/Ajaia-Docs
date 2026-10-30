<?php

namespace App\Filament\Widgets;

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\DocumentShare;
use Filament\Widgets\ChartWidget;

class AccessBreakdownChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Access breakdown';

    protected ?string $description = 'How documents are shared across the workspace.';

    protected ?string $maxHeight = '260px';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $roles = DocumentShare::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $private = Document::doesntHave('shares')->count();

        return [
            'datasets' => [
                [
                    'data' => [
                        (int) ($roles[ShareRole::Editor->value] ?? 0),
                        (int) ($roles[ShareRole::Viewer->value] ?? 0),
                        $private,
                    ],
                    'backgroundColor' => ['#2563eb', '#93c5fd', '#e2e8f0'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Editor shares', 'Viewer shares', 'Private documents'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'cutout' => '68%',
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
