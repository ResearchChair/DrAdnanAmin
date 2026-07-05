<?php

namespace App\Services;

use App\Models\Publication;
use App\Models\Profile;

class PublicationSyncService
{
    public function __construct(
        protected OrcidSyncService $orcidSync,
        protected OpenAlexService $openAlex,
    ) {}

    public function syncFromOrcid(?string $orcidId = null): array
    {
        return $this->orcidSync->sync($orcidId);
    }

    public function enrichAll(): array
    {
        $enriched = 0;
        $failed = 0;

        Publication::query()->whereNotNull('doi')->chunkById(50, function ($publications) use (&$enriched, &$failed) {
            foreach ($publications as $publication) {
                if ($this->openAlex->enrichPublication($publication)) {
                    $enriched++;
                } else {
                    $failed++;
                }
            }
        });

        return compact('enriched', 'failed');
    }

    public function updatePublicationCount(): void
    {
        $profile = Profile::query()->first();

        if (! $profile || ! $profile->citationStats) {
            return;
        }

        $profile->citationStats->update([
            'publication_count' => Publication::query()->visible()->count(),
        ]);
    }
}
