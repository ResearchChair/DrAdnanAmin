<?php

namespace App\Console\Commands;

use App\Models\Profile;
use App\Services\OrcidSyncService;
use App\Support\AcademicHttp;
use Illuminate\Console\Command;

class TestOrcidCommand extends Command
{
    protected $signature = 'portfolio:test-orcid {orcid?}';

    protected $description = 'Diagnose ORCID works API connectivity';

    public function handle(): int
    {
        $raw = $this->argument('orcid')
            ?? Profile::query()->value('orcid_id')
            ?? config('academic.orcid_id');

        if (! $raw) {
            $this->error('No ORCID ID configured.');

            return self::FAILURE;
        }

        $orcid = OrcidSyncService::normalizeOrcid($raw);

        if (! $orcid) {
            $this->error("Invalid ORCID format: {$raw}");

            return self::FAILURE;
        }

        if (OrcidSyncService::isPlaceholderOrcid($orcid)) {
            $this->error('ORCID is still the placeholder 0000-0000-0000-0000. Use your real ID from orcid.org.');

            return self::FAILURE;
        }

        $url = "https://pub.orcid.org/v3.0/{$orcid}/works";

        $this->info("ORCID: {$orcid}");
        $this->line("URL: {$url}");
        $this->line('SSL verify: '.json_encode(AcademicHttp::verifyOption()));

        try {
            $response = AcademicHttp::externalClient()
                ->withHeaders(['Accept' => 'application/json'])
                ->get($url);

            $this->line('Status: '.$response->status());

            if ($response->successful()) {
                $count = count($response->json()['group'] ?? []);
                $this->info("Works groups: {$count}");

                return self::SUCCESS;
            }

            $this->error(substr($response->body(), 0, 400));

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Exception: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
