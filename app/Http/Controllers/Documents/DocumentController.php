<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentRequest;
use App\Http\Requests\Documents\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $owned = $user->ownedDocuments()
            ->withCount('shares')
            ->latest('updated_at')
            ->get(['id', 'owner_id', 'title', 'updated_at']);

        $shared = $user->sharedDocuments()
            ->with('owner:id,name')
            ->latest('documents.updated_at')
            ->get(['documents.id', 'documents.owner_id', 'documents.title', 'documents.updated_at']);

        return Inertia::render('documents/Index', [
            'owned' => $owned->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'updated_at' => $document->updated_at,
                'shares_count' => $document->shares_count,
            ]),
            'shared' => $shared->map(fn (Document $document) => [
                'id' => $document->id,
                'title' => $document->title,
                'updated_at' => $document->updated_at,
                'owner' => $document->owner->name,
                'role' => $document->pivot->role,
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
        Gate::authorize('view', $document);

        $user = $request->user();
        $document->load(['owner:id,name', 'shares.user:id,name,email']);

        return Inertia::render('documents/Show', [
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'content' => $document->content,
                'updated_at' => $document->updated_at,
                'owner' => $document->owner->only(['id', 'name']),
            ],
            'permissions' => [
                'update' => $user->can('update', $document),
                'share' => $user->can('share', $document),
                'delete' => $user->can('delete', $document),
            ],
            'shares' => $document->shares->map(fn ($share) => [
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

    public function destroy(Document $document): RedirectResponse
    {
        Gate::authorize('delete', $document);

        $document->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Document deleted.']);

        return to_route('documents.index');
    }
}
