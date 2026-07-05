<?php

namespace App\Console\Commands;

use App\Services\PublicationSyncService;
use Illuminate\Console\Command;

class SyncPublicationsCommand extends Command
{
    protected $signature = 'publications:sync {--orcid=} {--enrich}';

    protected $description = 'Sync publications from ORCID and optionally enrich via OpenAlex';

    public function handle(PublicationSyncService $syncService): int
    {
        $result = $syncService->syncFromOrcid($this->option('orcid'));
        $this->info("ORCID: added {$result['added']}, updated {$result['updated']}, skipped {$result['skipped']}");

        if ($this->option('enrich')) {
            $enrich = $syncService->enrichAll();
            $this->info("OpenAlex: enriched {$enrich['enriched']}, failed {$enrich['failed']}");
        }

        $syncService->updatePublicationCount();

        return self::SUCCESS;
    }
}
