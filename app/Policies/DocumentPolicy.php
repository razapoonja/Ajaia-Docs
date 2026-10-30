<?php

namespace App\Policies;

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\User;
use Filament\Facades\Filament;

class DocumentPolicy
{
    /**
     * Admins can manage any document, but only inside the admin panel.
     * In the app they follow the same sharing rules as everyone else,
     * so a document shared with an admin as a viewer stays read-only.
     */
    public function before(User $user): ?bool
    {
        return $user->is_admin && Filament::isServing() ? true : null;
    }

    /**
     * Owner or anyone the document has been shared with may view it.
     */
    public function view(User $user, Document $document): bool
    {
        return $document->isOwnedBy($user) || $document->shareRoleFor($user) !== null;
    }

    /**
     * Owner or a collaborator with the editor role may change title/content.
     */
    public function update(User $user, Document $document): bool
    {
        return $document->isOwnedBy($user) || $document->shareRoleFor($user) === ShareRole::Editor;
    }

    /**
     * Only the owner may delete or manage sharing.
     */
    public function delete(User $user, Document $document): bool
    {
        return $document->isOwnedBy($user);
    }

    public function share(User $user, Document $document): bool
    {
        return $document->isOwnedBy($user);
    }
}
