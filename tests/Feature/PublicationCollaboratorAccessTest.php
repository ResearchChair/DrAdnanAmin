<?php

namespace Tests\Feature;

use App\Models\Publication;
use App\Models\PublicationCollaborator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicationCollaboratorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_collaborator_link_shows_only_matching_publications(): void
    {
        $matching = Publication::query()->create([
            'title' => 'Matching Paper',
            'type' => 'journal',
            'status' => 'accepted',
            'is_visible' => true,
        ]);

        $other = Publication::query()->create([
            'title' => 'Other Paper',
            'type' => 'conference',
            'status' => 'published',
            'is_visible' => true,
        ]);

        PublicationCollaborator::query()->create([
            'publication_id' => $matching->id,
            'email' => 'coauthor@example.com',
            'token_hash' => hash('sha256', 'plain-token'),
            'expires_at' => now()->addHours(2),
        ]);

        PublicationCollaborator::query()->create([
            'publication_id' => $other->id,
            'email' => 'another@example.com',
            'token_hash' => hash('sha256', 'other-token'),
            'expires_at' => now()->addHours(2),
        ]);

        $collaborator = PublicationCollaborator::query()->where('email', 'coauthor@example.com')->firstOrFail();

        $url = URL::temporarySignedRoute(
            'publications.collaborator',
            now()->addHours(2),
            [
                'collaborator' => $collaborator->id,
                'token' => 'plain-token',
            ]
        );

        $response = $this->get($url);

        $response->assertOk();
        $response->assertSee('Matching Paper');
        $response->assertDontSee('Other Paper');
    }

    public function test_expired_or_invalid_token_is_denied(): void
    {
        $publication = Publication::query()->create([
            'title' => 'Protected Paper',
            'type' => 'journal',
            'status' => 'published',
            'is_visible' => true,
        ]);

        $collaborator = PublicationCollaborator::query()->create([
            'publication_id' => $publication->id,
            'email' => 'coauthor@example.com',
            'token_hash' => hash('sha256', 'right-token'),
            'expires_at' => now()->subMinute(),
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'publications.collaborator',
            now()->addMinutes(10),
            [
                'collaborator' => $collaborator->id,
                'token' => 'right-token',
            ]
        );

        $this->get($expiredUrl)->assertForbidden();

        $freshUrlWrongToken = URL::temporarySignedRoute(
            'publications.collaborator',
            now()->addMinutes(10),
            [
                'collaborator' => $collaborator->id,
                'token' => 'wrong-token',
            ]
        );

        $this->get($freshUrlWrongToken)->assertForbidden();
    }

    public function test_public_publications_page_still_works_with_status_field(): void
    {
        Publication::query()->create([
            'title' => 'Visible Paper',
            'type' => 'journal',
            'status' => 'under_review',
            'is_visible' => true,
        ]);

        $response = $this->get(route('publications'));

        $response->assertOk();
        $response->assertSee('Visible Paper');
        $response->assertSee('Under Review');
    }
}
