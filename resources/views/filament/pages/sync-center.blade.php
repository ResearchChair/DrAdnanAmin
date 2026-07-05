<x-filament-panels::page>
    <form wire:submit.prevent="syncOrcid">
        {{ $this->form }}

        @if($lastSynced = $this->getLastSyncedLabel())
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                Last ORCID sync: <strong>{{ $lastSynced }}</strong>
            </p>
        @endif

        <div class="mt-6 flex flex-wrap gap-3">
            <x-filament::button type="button" wire:click="syncOrcid" color="primary">
                Sync from ORCID now
            </x-filament::button>

            <x-filament::button type="button" wire:click="enrichOpenAlex" color="gray">
                Enrich via OpenAlex
            </x-filament::button>

            <x-filament::button type="button" wire:click="importBibtex" color="success">
                Import BibTeX
            </x-filament::button>
        </div>
    </form>

    <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="text-lg font-semibold">How publication sync works</h3>
        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-gray-600 dark:text-gray-300">
            <li><strong>ORCID</strong> is the primary source — works are imported when you save your ORCID ID and refreshed daily.</li>
            <li><strong>Manual entries</strong> remain available under Research → Publications → Add manually.</li>
            <li><strong>OpenAlex</strong> enriches imported works with authors, venue, and citation counts when a DOI is available.</li>
            <li><strong>BibTeX</strong> is optional for bulk imports from a file.</li>
        </ul>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="text-lg font-semibold">Citation Stats (Google Scholar)</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Google Scholar does not provide a public API. Update citation totals manually in
            <strong>Site Content → Profile</strong> under Citation Stats.
        </p>
    </div>
</x-filament-panels::page>
