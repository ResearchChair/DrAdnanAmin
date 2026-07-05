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
        $result = $syncService->runOrcidSync($this->option('orcid'), enrich: (bool) $this->option('enrich'));
        $this->info("ORCID: added {$result['added']}, updated {$result['updated']}, skipped {$result['skipped']}");

        if ($this->option('enrich') && isset($result['enriched'])) {
            $this->info("OpenAlex: enriched {$result['enriched']}, failed {$result['enrich_failed']}");
        }

        if (! empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                $this->error($error);
            }
        }

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
