<?php

namespace App\Models;

use App\Enums\ShareRole;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $title
 * @property string|null $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $owner
 */
#[Fillable(['owner_id', 'title', 'content'])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function sharedWith(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'document_shares')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function shareRoleFor(User $user): ?ShareRole
    {
        $share = $this->shares->firstWhere('user_id', $user->id)
            ?? $this->shares()->where('user_id', $user->id)->first();

        return $share?->role;
    }

    /**
     * Documents the user owns or has been granted access to.
     *
     * @param  Builder<Document>  $query
     * @return Builder<Document>
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('owner_id', $user->id)
                ->orWhereHas('shares', fn (Builder $q) => $q->where('user_id', $user->id));
        });
    }
}
