<?php

namespace App\Jobs;

use App\Services\PublicationSyncService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SyncPublicationsFromOrcidJob
{
    use Dispatchable;

    public function __construct(
        public ?string $orcidId = null,
    ) {}

    public function handle(PublicationSyncService $syncService): array
    {
        $result = $syncService->runOrcidSync($this->orcidId);

        if (! empty($result['errors'])) {
            Log::warning('ORCID publication sync completed with errors', $result);
        }

        return $result;
    }
}
