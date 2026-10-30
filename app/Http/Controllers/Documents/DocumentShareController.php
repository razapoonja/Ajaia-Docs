<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentShareRequest;
use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DocumentShareController extends Controller
{
    public function store(StoreDocumentShareRequest $request, Document $document): RedirectResponse
    {
        Gate::authorize('share', $document);

        $collaborator = User::where('email', $request->validated('email'))->firstOrFail();

        $document->shares()->updateOrCreate(
            ['user_id' => $collaborator->id],
            ['role' => $request->validated('role')],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => "Shared with {$collaborator->name}."]);

        return back();
    }

    public function destroy(Document $document, DocumentShare $share): RedirectResponse
    {
        Gate::authorize('share', $document);

        abort_unless($share->document_id === $document->id, 404);

        $share->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Access removed.']);

        return back();
    }
}
