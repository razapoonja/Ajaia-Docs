<?php

namespace App\Filament\Widgets;

use App\Enums\ShareRole;
use App\Filament\Resources\Documents\DocumentResource;
use App\Filament\Resources\DocumentShares\DocumentShareResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class WorkspaceStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $documents = Document::count();
        $sharedDocuments = Document::has('shares')->count();
        $shareCounts = DocumentShare::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');
        $editors = (int) ($shareCounts[ShareRole::Editor->value] ?? 0);
        $viewers = (int) ($shareCounts[ShareRole::Viewer->value] ?? 0);
        $sharingRate = $documents > 0 ? round($sharedDocuments / $documents * 100) : 0;

        return [
            Stat::make('Documents', number_format($documents))
                ->description($this->createdThisWeek(Document::class).' created this week')
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->icon(Heroicon::OutlinedDocumentText)
                ->chart($this->dailyCounts(Document::class))
                ->color('primary')
                ->url(DocumentResource::getUrl()),
            Stat::make('People', number_format(User::count()))
                ->description($this->createdThisWeek(User::class).' joined this week')
                ->descriptionIcon(Heroicon::OutlinedUserPlus)
                ->icon(Heroicon::OutlinedUsers)
                ->chart($this->dailyCounts(User::class))
                ->color('success')
                ->url(UserResource::getUrl()),
            Stat::make('Active shares', number_format($editors + $viewers))
                ->description("{$editors} can edit · {$viewers} can view")
                ->descriptionIcon(Heroicon::OutlinedShare)
                ->icon(Heroicon::OutlinedShare)
                ->chart($this->dailyCounts(DocumentShare::class))
                ->color('info')
                ->url(DocumentShareResource::getUrl()),
            Stat::make('Sharing rate', $sharingRate.'%')
                ->description("{$sharedDocuments} of {$documents} documents are shared")
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->icon(Heroicon::OutlinedUserGroup)
                ->color($sharingRate >= 50 ? 'success' : 'warning'),
        ];
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function createdThisWeek(string $model): int
    {
        return $model::query()->where('created_at', '>=', now()->subWeek())->count();
    }

    /**
     * Creations per day for the last 7 days, oldest first, zero-filled.
     *
     * @param  class-string<Model>  $model
     * @return array<int, int>
     */
    private function dailyCounts(string $model): array
    {
        $start = now()->subDays(6)->startOfDay();

        $counts = $model::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(0, 6))
            ->map(fn (int $offset): int => (int) ($counts[Carbon::parse($start)->addDays($offset)->toDateString()] ?? 0))
            ->all();
    }
}
