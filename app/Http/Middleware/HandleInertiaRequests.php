<?php

namespace App\Http\Middleware;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'isAdmin' => (bool) $request->user()?->is_admin,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'sidebar' => fn (): ?array => $request->user() ? $this->sidebar($request->user()) : null,
        ];
    }

    /**
     * Library counts and recent documents for the app sidebar.
     * Lazy, so partial reloads such as autosave never run these queries.
     *
     * @return array{counts: array{owned: int, shared: int}, recent: array<int, array{id: int, title: string}>}
     */
    private function sidebar(User $user): array
    {
        return [
            'counts' => [
                'owned' => $user->ownedDocuments()->count(),
                'shared' => $user->documentShares()->count(),
            ],
            'recent' => Document::query()
                ->accessibleBy($user)
                ->latest('updated_at')
                ->limit(5)
                ->get(['id', 'title'])
                ->map(fn (Document $document): array => ['id' => $document->id, 'title' => $document->title])
                ->all(),
        ];
    }
}
