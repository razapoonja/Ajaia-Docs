<?php

namespace Database\Seeders;

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Three demo accounts (password: "password") with a few documents
     * and shares so reviewers can see owned vs shared behaviour immediately.
     * Alice is also an admin and can open /admin.
     */
    public function run(): void
    {
        $alice = User::factory()->create(['name' => 'Alice Owner', 'email' => 'alice@example.com', 'is_admin' => true]);
        $bob = User::factory()->create(['name' => 'Bob Editor', 'email' => 'bob@example.com']);
        $carol = User::factory()->create(['name' => 'Carol Viewer', 'email' => 'carol@example.com']);

        $roadmap = Document::factory()->for($alice, 'owner')->create([
            'title' => 'Q4 product roadmap',
            'content' => '<h1>Q4 product roadmap</h1><p>Shared with Bob (can edit) and Carol (can view).</p><ul><li><p>Ship document sharing</p></li><li><p>Add file import</p></li></ul>',
        ]);

        $roadmap->shares()->createMany([
            ['user_id' => $bob->id, 'role' => ShareRole::Editor],
            ['user_id' => $carol->id, 'role' => ShareRole::Viewer],
        ]);

        Document::factory()->for($alice, 'owner')->create([
            'title' => 'Private notes',
            'content' => '<p>Only Alice can see this document.</p>',
        ]);

        Document::factory()->for($bob, 'owner')->create([
            'title' => 'Meeting notes',
            'content' => '<h2>Meeting notes</h2><p><strong>Bold</strong>, <em>italic</em> and <u>underline</u> all persist.</p>',
        ]);
    }
}
