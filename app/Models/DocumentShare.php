<?php

namespace App\Models;

use App\Enums\ShareRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $document_id
 * @property int $user_id
 * @property ShareRole $role
 * @property-read Document $document
 * @property-read User $user
 */
#[Fillable(['document_id', 'user_id', 'role'])]
class DocumentShare extends Model
{
    protected function casts(): array
    {
        return [
            'role' => ShareRole::class,
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
