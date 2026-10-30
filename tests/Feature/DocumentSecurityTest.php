<?php

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\User;

test('guests cannot reach documents', function () {
    $this->get(route('documents.index'))->assertRedirect(route('login'));
});

test('stored content is reduced to editor-safe html', function () {
    $owner = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)->patch(route('documents.update', $document), [
        'content' => '<p onclick="steal()" style="color:red">Hi <strong>there</strong></p>'
            .'<script>alert(1)</script><img src="x" onerror="alert(2)">'
            .'<div><span>Kept text</span></div>',
    ]);

    $content = $document->fresh()->content;

    expect($content)
        ->toContain('<p>Hi <strong>there</strong></p>')
        ->toContain('Kept text')
        ->not->toContain('script')
        ->not->toContain('alert')
        ->not->toContain('onclick')
        ->not->toContain('onerror')
        ->not->toContain('style=')
        ->not->toContain('<div');
});

test('an empty title is rejected', function () {
    $owner = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create(['title' => 'Keep me']);

    $this->actingAs($owner)
        ->patch(route('documents.update', $document), ['title' => ''])
        ->assertSessionHasErrors('title');

    expect($document->fresh()->title)->toBe('Keep me');
});

test('sharing requires an existing account that is not the owner', function () {
    $owner = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)
        ->post(route('documents.shares.store', $document), ['email' => 'nobody@example.com', 'role' => 'viewer'])
        ->assertSessionHasErrors('email');

    $this->actingAs($owner)
        ->post(route('documents.shares.store', $document), ['email' => $owner->email, 'role' => 'viewer'])
        ->assertSessionHasErrors('email');

    expect($document->shares()->count())->toBe(0);
});

test('sharing again with the same person updates the role instead of duplicating', function () {
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)->post(route('documents.shares.store', $document), ['email' => $collaborator->email, 'role' => 'viewer']);
    $this->actingAs($owner)->post(route('documents.shares.store', $document), ['email' => $collaborator->email, 'role' => 'editor']);

    expect($document->shares()->count())->toBe(1)
        ->and($document->shares()->first()->role)->toBe(ShareRole::Editor);
});

test('a share cannot be revoked through another document', function () {
    $owner = User::factory()->create();
    $mine = Document::factory()->for($owner, 'owner')->create();
    $theirs = Document::factory()->create();
    $share = $theirs->shares()->create(['user_id' => User::factory()->create()->id, 'role' => ShareRole::Viewer]);

    $this->actingAs($owner)
        ->delete(route('documents.shares.destroy', ['document' => $mine, 'share' => $share]))
        ->assertNotFound();

    $this->assertModelExists($share);
});

test('only the owner can delete a document', function () {
    $document = Document::factory()->create();
    $editor = User::factory()->create();
    $document->shares()->create(['user_id' => $editor->id, 'role' => ShareRole::Editor]);

    $this->actingAs($editor)
        ->delete(route('documents.destroy', $document))
        ->assertForbidden();

    $this->actingAs($document->owner)
        ->delete(route('documents.destroy', $document))
        ->assertRedirect(route('documents.index'));

    $this->assertModelMissing($document);
});

test('checklists and safe links survive sanitizing while script links do not', function () {
    $owner = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create();

    $this->actingAs($owner)->patch(route('documents.update', $document), [
        'content' => '<ul data-type="taskList"><li data-checked="true" data-type="taskItem"><label><input type="checkbox" checked><span></span></label><div><p>Done</p></div></li></ul>'
            .'<p><a href="https://example.com" onclick="x()">safe</a> <a href="javascript:alert(1)">unsafe</a></p>'
            .'<blockquote><p>Quote</p></blockquote><pre><code>code()</code></pre><hr>',
    ]);

    $content = $document->fresh()->content;

    expect($content)
        ->toContain('data-type="taskList"')
        ->toContain('data-checked="true"')
        ->toContain('href="https://example.com"')
        ->toContain('rel="noopener noreferrer nofollow"')
        ->toContain('<blockquote>')
        ->toContain('<pre><code>code()</code></pre>')
        ->toContain('<hr')
        ->not->toContain('javascript:')
        ->not->toContain('onclick')
        ->not->toContain('<input');
});

test('a forbidden document shows the branded error page', function () {
    $document = Document::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('documents.show', $document))
        ->assertForbidden()
        ->assertInertia(fn ($page) => $page->component('ErrorPage')->where('status', 403));
});

test('sharing is rate limited', function () {
    $owner = User::factory()->create();
    $document = Document::factory()->for($owner, 'owner')->create();

    foreach (range(1, 30) as $attempt) {
        $this->actingAs($owner)->post(route('documents.shares.store', $document), ['email' => "probe{$attempt}@example.com", 'role' => 'viewer']);
    }

    $this->actingAs($owner)
        ->post(route('documents.shares.store', $document), ['email' => 'probe@example.com', 'role' => 'viewer'])
        ->assertTooManyRequests();
});
