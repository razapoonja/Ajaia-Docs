<?php

namespace Database\Seeders;

use App\Enums\ShareRole;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Demo accounts (password: "password"):
     * - alice@example.com owns most of the workspace and is an admin.
     * - bob@example.com can edit the Q4 roadmap.
     * - carol@example.com can only view it.
     * A named background team gives the dashboard 30 days of history.
     */
    public function run(): void
    {
        $alice = User::factory()->create(['name' => 'Alice Owner', 'email' => 'alice@example.com', 'is_admin' => true, 'created_at' => now()->subDays(45)]);
        $bob = User::factory()->create(['name' => 'Bob Editor', 'email' => 'bob@example.com', 'created_at' => now()->subDays(40)]);
        $carol = User::factory()->create(['name' => 'Carol Viewer', 'email' => 'carol@example.com', 'created_at' => now()->subDays(38)]);

        $team = $this->seedTeam();

        $this->seedAliceDocuments($alice, $bob, $carol, $team);
        $this->seedBobDocuments($bob);
        $this->seedTeamDocuments($team, $alice);
    }

    /**
     * @return Collection<string, User>
     */
    private function seedTeam(): Collection
    {
        $names = ['Maya Chen', 'Daniel Okafor', 'Priya Nair', 'Lucas Moreau', 'Sara Ahmed', 'Tom Becker', 'Elena Rossi', 'Omar Haddad'];

        return collect($names)->mapWithKeys(function (string $name, int $index) {
            $first = strtolower(explode(' ', $name)[0]);

            return [$first => User::factory()->create([
                'name' => $name,
                'email' => "{$first}@example.com",
                'created_at' => now()->subDays(32 - $index * 4),
            ])];
        });
    }

    /**
     * @param  Collection<string, User>  $team
     */
    private function seedAliceDocuments(User $alice, User $bob, User $carol, Collection $team): void
    {
        $roadmap = $this->document($alice, 'Q4 product roadmap', 1, <<<'HTML'
            <h1>Q4 product roadmap</h1>
            <p>Shared with <strong>Bob</strong> (can edit) and <strong>Carol</strong> (can view). Log in as each of them to see the difference.</p>
            <h2>Themes</h2>
            <ol><li><p><strong>Collaboration.</strong> Sharing with view and edit roles.</p></li><li><p><strong>Import.</strong> Bring in .txt, .md and .docx files.</p></li><li><p><strong>Admin.</strong> One place to see people, documents and access.</p></li></ol>
            <h2>This sprint</h2>
            <ul data-type="taskList"><li data-type="taskItem" data-checked="true"><p>Ship document sharing</p></li><li data-type="taskItem" data-checked="true"><p>Add file import</p></li><li data-type="taskItem" data-checked="false"><p>Real-time presence</p></li></ul>
            <blockquote><p>Keep the scope small and the details right.</p></blockquote>
            HTML);
        $this->share($roadmap, $bob, ShareRole::Editor, 1);
        $this->share($roadmap, $carol, ShareRole::Viewer, 1);

        $this->document($alice, 'Private notes', 2, '<p>Only Alice can see this document. It has never been shared, so it shows as private in the admin panel.</p>');

        $launch = $this->document($alice, 'Launch plan: Ajaia Docs v1', 6, <<<'HTML'
            <h1>Launch plan</h1>
            <p>Target date is the end of the month. Owner: Alice. Reviewers: Maya and Daniel.</p>
            <h2>Timeline</h2>
            <ol><li><p>Freeze features and write release notes</p></li><li><p>Internal dogfooding for one week</p></li><li><p>Invite the first ten teams</p></li></ol>
            <h2>Checklist</h2>
            <ul data-type="taskList"><li data-type="taskItem" data-checked="true"><p>Landing page copy</p></li><li data-type="taskItem" data-checked="false"><p>Support macros</p></li><li data-type="taskItem" data-checked="false"><p>Status page</p></li></ul>
            <p>Background reading: <a href="https://laravel.com/docs">Laravel documentation</a>.</p>
            HTML);
        $this->share($launch, $team['maya'], ShareRole::Editor, 5);
        $this->share($launch, $team['daniel'], ShareRole::Viewer, 5);

        $handbook = $this->document($alice, 'Team handbook', 18, <<<'HTML'
            <h1>Team handbook</h1>
            <h2>How we write</h2>
            <ul><li><p>Lead with the decision, then the reasoning.</p></li><li><p>Use plain words. Cut anything that does not help the reader act.</p></li><li><p>Link to the source instead of restating it.</p></li></ul>
            <h2>How we meet</h2>
            <p>Every meeting has a document. If there is no document, there is no meeting.</p>
            <hr>
            <p><em>Last reviewed by Alice.</em></p>
            HTML);
        foreach (['priya', 'lucas', 'sara'] as $index => $member) {
            $this->share($handbook, $team[$member], ShareRole::Viewer, 17 - $index * 3);
        }

        $this->document($alice, 'Interview scorecard', 9, <<<'HTML'
            <h1>Interview scorecard</h1>
            <p>Use this for every product engineer loop.</p>
            <ul data-type="taskList"><li data-type="taskItem" data-checked="false"><p>Turns a vague brief into a scoped plan</p></li><li data-type="taskItem" data-checked="false"><p>Explains tradeoffs without being asked</p></li><li data-type="taskItem" data-checked="false"><p>Uses AI tools without outsourcing judgment</p></li></ul>
            HTML);

        $update = $this->document($alice, 'Weekly update, week 44', 3, <<<'HTML'
            <h1>Weekly update</h1>
            <h2>Shipped</h2>
            <ul><li><p>Sharing with view and edit roles</p></li><li><p>Markdown and PDF export</p></li></ul>
            <h2>Next</h2>
            <ul><li><p>Version history</p></li><li><p>Comments</p></li></ul>
            <p><s>Real-time cursors</s> moved to next quarter.</p>
            HTML);
        $this->share($update, $team['tom'], ShareRole::Editor, 3);

        $research = $this->document($alice, 'Customer research notes', 13, <<<'HTML'
            <h1>Customer research notes</h1>
            <p>Five interviews with team leads at small companies.</p>
            <blockquote><p>We just want to hand a doc to someone and know whether they can change it.</p></blockquote>
            <blockquote><p>Importing our old Word files is the first thing we would try.</p></blockquote>
            <h2>Takeaways</h2>
            <ol><li><p>Clear roles beat fine-grained permissions.</p></li><li><p>Import has to work on day one.</p></li></ol>
            HTML);
        $this->share($research, $team['elena'], ShareRole::Viewer, 12);

        $this->document($alice, 'API design notes', 21, <<<'HTML'
            <h1>API design notes</h1>
            <p>Every document route goes through <code>DocumentPolicy</code>. Sharing uses an upsert so re-sharing changes the role instead of duplicating it.</p>
            <pre><code>$document-&gt;shares()-&gt;updateOrCreate(
                ['user_id' =&gt; $collaborator-&gt;id],
                ['role' =&gt; $role],
            );</code></pre>
            <p>Content is sanitized on write by <code>SanitizeDocumentHtml</code>.</p>
            HTML);
    }

    private function seedBobDocuments(User $bob): void
    {
        $this->document($bob, 'Meeting notes', 4, '<h2>Meeting notes</h2><p><strong>Bold</strong>, <em>italic</em> and <u>underline</u> all persist.</p>');
    }

    /**
     * Team documents never involve Bob or Carol, so their lists stay focused on the roadmap.
     *
     * @param  Collection<string, User>  $team
     */
    private function seedTeamDocuments(Collection $team, User $alice): void
    {
        $titles = [
            'Hiring plan', 'Onboarding checklist', 'Launch announcement draft', 'Pricing page copy',
            'Incident postmortem', 'Weekly team update', 'Support macros', 'Board update outline',
            'Partner integration spec', 'Release notes', 'Research synthesis', 'Marketing calendar',
            'Retro notes', 'Sales playbook', 'Analytics glossary',
        ];

        $members = $team->values();

        foreach ($titles as $index => $title) {
            $owner = $members[$index % $members->count()];
            $daysAgo = 29 - (int) round($index * 28 / (count($titles) - 1));
            $document = $this->document($owner, $title, $daysAgo, $this->teamDocumentBody($title));

            $members->reject(fn (User $user) => $user->is($owner))
                ->shuffle()
                ->take(random_int(0, 3))
                ->each(fn (User $user) => $this->share($document, $user, random_int(0, 2) === 0 ? ShareRole::Editor : ShareRole::Viewer, max(0, $daysAgo - 1)));
        }

        foreach ([['Design system audit', 'maya', ShareRole::Editor, 8], ['Quarterly OKRs', 'daniel', ShareRole::Editor, 11], ['Security review checklist', 'omar', ShareRole::Viewer, 2]] as [$title, $owner, $role, $daysAgo]) {
            $document = $this->document($team[$owner], $title, $daysAgo, $this->teamDocumentBody($title));
            $this->share($document, $alice, $role, $daysAgo);
        }
    }

    private function teamDocumentBody(string $title): string
    {
        $openers = [
            'This is the working draft. Leave suggestions inline and the owner will fold them in on Friday.',
            'Summary of where things stand, what changed since last week, and what we need decided.',
            'A short plan with owners and dates. Anything without an owner is not happening yet.',
            'Notes from the last session, cleaned up so people who missed it can catch up in five minutes.',
        ];

        $points = [
            'Agree on the scope before the next review.',
            'Keep the first version small and ship it.',
            'Write down the decision and who made it.',
            'Flag anything blocked for more than two days.',
            'Link the source instead of copying it here.',
            'Check the numbers with finance before sharing.',
        ];

        $picked = collect($points)->shuffle()->take(3)->map(fn (string $point) => "<li><p>{$point}</p></li>")->implode('');

        return '<h1>'.e($title).'</h1><p>'.$openers[array_rand($openers)].'</p><ul>'.$picked.'</ul>';
    }

    private function document(User $owner, string $title, int $daysAgo, string $content): Document
    {
        $createdAt = now()->subDays($daysAgo)->subHours(random_int(1, 9));

        return Document::factory()->for($owner, 'owner')->create([
            'title' => $title,
            'content' => preg_replace('/>\s+</', '><', trim($content)),
            'created_at' => $createdAt,
            'updated_at' => $createdAt->addHours(random_int(1, 30))->min(now()),
        ]);
    }

    private function share(Document $document, User $user, ShareRole $role, int $daysAgo): void
    {
        $document->shares()->forceCreate([
            'user_id' => $user->id,
            'role' => $role,
            'created_at' => now()->subDays($daysAgo)->min(now()),
            'updated_at' => now()->subDays($daysAgo)->min(now()),
        ]);
    }
}
