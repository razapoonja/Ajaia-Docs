<?php

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\User;

test('a stranger cannot view another user\'s document', function () {
    $document = Document::factory()->create();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->get(route('documents.show', $document))
        ->assertForbidden();
});

test('a viewer can open a shared document but cannot edit it', function () {
    $document = Document::factory()->create();
    $viewer = User::factory()->create();
    $document->shares()->create(['user_id' => $viewer->id, 'role' => ShareRole::Viewer]);

    $this->actingAs($viewer)
        ->get(route('documents.show', $document))
        ->assertOk();

    $this->actingAs($viewer)
        ->patch(route('documents.update', $document), ['title' => 'Hijacked'])
        ->assertForbidden();

    expect($document->fresh()->title)->not->toBe('Hijacked');
});

test('an editor can update a shared document but cannot share it onward', function () {
    $document = Document::factory()->create();
    $editor = User::factory()->create();
    $third = User::factory()->create();
    $document->shares()->create(['user_id' => $editor->id, 'role' => ShareRole::Editor]);

    $this->actingAs($editor)
        ->patch(route('documents.update', $document), ['content' => '<p>Edited</p>'])
        ->assertRedirect();

    expect($document->fresh()->content)->toBe('<p>Edited</p>');

    $this->actingAs($editor)
        ->post(route('documents.shares.store', $document), ['email' => $third->email, 'role' => 'viewer'])
        ->assertForbidden();
});

test('the owner can share a document and the share appears in the recipient\'s list', function () {
    $owner = User::factory()->create();
    $recipient = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create(['title' => 'Shared doc']);

    $this->actingAs($owner)
        ->post(route('documents.shares.store', $document), ['email' => $recipient->email, 'role' => 'editor'])
        ->assertRedirect();

    $this->assertDatabaseHas('document_shares', [
        'document_id' => $document->id,
        'user_id' => $recipient->id,
        'role' => 'editor',
    ]);

    $this->actingAs($recipient)
        ->get(route('documents.index'))
        ->assertInertia(fn ($page) => $page
            ->component('documents/Index')
            ->has('shared', 1)
            ->where('shared.0.title', 'Shared doc')
            ->where('shared.0.role', 'editor'));
});

test('document content survives a round trip', function () {
    $owner = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create();
    $html = '<h1>Title</h1><p><strong>bold</strong> <em>italic</em></p><ul><li><p>item</p></li></ul>';

    $this->actingAs($owner)->patch(route('documents.update', $document), ['content' => $html]);

    $this->actingAs($owner)
        ->get(route('documents.show', $document))
        ->assertInertia(fn ($page) => $page->where('document.content', $html));
});
