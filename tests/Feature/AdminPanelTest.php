<?php

use App\Models\Document;
use App\Models\User;

test('a regular user cannot open the admin panel', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('an admin can open the admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('an admin can manage any document', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $document = Document::factory()->create();

    expect($admin->can('update', $document))->toBeTrue()
        ->and($admin->can('delete', $document))->toBeTrue();
});
