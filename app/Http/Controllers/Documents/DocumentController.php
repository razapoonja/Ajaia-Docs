<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentRequest;
use App\Http\Requests\Documents\UpdateDocumentRequest;
use App\Models\Document;
use App\Models\DocumentShare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    private const PREVIEW_LENGTH = 2000;

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Previews only need the start of each body, so the full HTML never leaves the database.
        $owned = $user->ownedDocuments()
            ->select(['id', 'owner_id', 'title', 'updated_at'])
            ->selectRaw('substr(content, 1, ?) as content', [self::PREVIEW_LENGTH])
            ->withCount('shares')
            ->latest('updated_at')
            ->get();

        $shared = $user->sharedDocuments()
            ->select(['documents.id', 'documents.owner_id', 'documents.title', 'documents.updated_at'])
            ->selectRaw('substr(documents.content, 1, ?) as content', [self::PREVIEW_LENGTH])
            ->with('owner:id,name')
            ->latest('documents.updated_at')
            ->get();

        return Inertia::render('documents/Index', [
            'owned' => $owned->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'excerpt' => $document->excerpt(),
                'updated_at' => $document->updated_at,
                'shares_count' => $document->shares_count,
            ]),
            'shared' => $shared->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'excerpt' => $document->excerpt(),
                'updated_at' => $document->updated_at,
                'owner' => $document->owner->name,
                'role' => $document->pivot->getAttribute('role'),
            ]),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = $request->user()->ownedDocuments()->create([
            'title' => $request->validated('title') ?: 'Untitled document',
            'content' => $request->validated('content'),
        ]);

        return to_route('documents.show', $document);
    }

    public function show(Request $request, Document $document): Response
    {
        // Loading shares first lets the policy check them in memory instead of querying again.
        $document->load(['owner:id,name', 'shares.user:id,name,email']);

        Gate::authorize('view', $document);

        $user = $request->user();

        // Closures let autosave request a partial reload without re-sending the whole document.
        return Inertia::render('documents/Show', [
            'document' => fn (): array => [
                'id' => $document->id,
                'title' => $document->title,
                'content' => $document->content,
                'updated_at' => $document->updated_at,
                'owner' => $document->owner->only(['id', 'name']),
            ],
            'permissions' => fn (): array => [
                'update' => $user->can('update', $document),
                'share' => $user->can('share', $document),
                'delete' => $user->can('delete', $document),
            ],
            'shares' => fn () => $document->shares->map(fn (DocumentShare $share): array => [
                'id' => $share->id,
                'role' => $share->role->value,
                'user' => $share->user->only(['id', 'name', 'email']),
            ]),
        ]);
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        Gate::authorize('update', $document);

        $document->update($request->validated());

        return back();
    }

    /**
     * Anyone who can open a document can copy it into their own library.
     * The copy belongs to them and starts unshared.
     */
    public function duplicate(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('view', $document);

        $copy = $request->user()->ownedDocuments()->create([
            'title' => Str::limit('Copy of '.$document->title, 200, ''),
            'content' => $document->content,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Copy created. It is yours and not shared with anyone.']);

        return to_route('documents.show', $copy);
    }

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        $document->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Document deleted.']);

        return to_route('documents.index');
    }
}
