<?php

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\User;

test('a regular user cannot open the admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('an admin can manage any document', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $document = Document::factory()->create();

    expect($admin->can('update', $document))->toBeTrue()
        ->and($admin->can('delete', $document))->toBeTrue();
});

test('every admin page renders for an admin', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $member = User::factory()->create();
    $document = Document::factory()->for($admin, 'owner')->create();
    $document->shares()->create(['user_id' => $member->id, 'role' => ShareRole::Editor]);

    $this->actingAs($admin);

    collect([
        '/admin',
        '/admin/documents',
        '/admin/documents/create',
        "/admin/documents/{$document->id}",
        "/admin/documents/{$document->id}/edit",
        '/admin/users',
        '/admin/users/create',
        "/admin/users/{$member->id}",
        "/admin/users/{$member->id}/edit",
        '/admin/document-shares',
    ])->each(fn (string $url) => $this->get($url)->assertOk());
});
