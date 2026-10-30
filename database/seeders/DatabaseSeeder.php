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
     * Three demo accounts (password: "password") tell the sharing story;
     * a small background team gives the admin dashboard realistic history.
     * Alice is also an admin and can open /admin.
     */
    public function run(): void
    {
        $this->seedDemoAccounts();
        $this->seedBackgroundTeam();
    }

    private function seedDemoAccounts(): void
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

    /**
     * Shares stay inside the background team so the demo accounts'
     * "Shared with you" lists only show the roadmap.
     */
    private function seedBackgroundTeam(): void
    {
        $titles = [
            'Hiring plan', 'Onboarding checklist', 'Launch announcement draft', 'Customer interview notes',
            'Pricing page copy', 'Incident postmortem', 'API design review', 'Weekly team update',
            'Design system audit', 'Quarterly OKRs', 'Support macros', 'Board update outline',
            'Partner integration spec', 'Release notes', 'Research synthesis', 'Security review checklist',
            'Marketing calendar', 'Retro notes', 'Sales playbook', 'Analytics glossary',
        ];

        $team = collect(range(1, 8))->map(fn (int $i) => User::factory()->create([
            'created_at' => now()->subDays(30 - $i * 3),
        ]));

        foreach ($titles as $index => $title) {
            $owner = $team[$index % $team->count()];
            $createdAt = now()->subDays(29 - $index)->subHours(random_int(0, 10));

            $document = Document::factory()->for($owner, 'owner')->create([
                'title' => $title,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addHours(random_int(1, 48))->min(now()),
            ]);

            $team->reject(fn (User $user) => $user->is($owner))
                ->shuffle()
                ->take(random_int(0, 3))
                ->each(fn (User $user) => $document->shares()->forceCreate([
                    'user_id' => $user->id,
                    'role' => random_int(0, 2) === 0 ? ShareRole::Editor : ShareRole::Viewer,
                    'created_at' => $createdAt->copy()->addHours(random_int(1, 24))->min(now()),
                ]));
        }
    }
}
