<?php

namespace App\Models;

use App\Actions\SanitizeDocumentHtml;
use App\Enums\ShareRole;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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

    /**
     * Content is sanitized on every write, whichever path it arrives by.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function content(): Attribute
    {
        return Attribute::make(set: fn (?string $value): ?string => app(SanitizeDocumentHtml::class)($value));
    }

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

    /**
     * Plain-text preview of the document body for list views.
     */
    public function excerpt(int $limit = 180): string
    {
        $text = html_entity_decode(strip_tags(str_replace(['</p>', '</li>', '</h1>', '</h2>', '</h3>', '<br>'], ' ', (string) $this->content)));

        $text = Str::squish($text);

        if ($this->title !== '' && Str::startsWith($text, $this->title)) {
            $text = ltrim(Str::after($text, $this->title));
        }

        return Str::limit($text, $limit);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function shareRoleFor(User $user): ?ShareRole
    {
        $share = $this->relationLoaded('shares')
            ? $this->shares->firstWhere('user_id', $user->id)
            : $this->shares()->where('user_id', $user->id)->first(['id', 'role']);

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
