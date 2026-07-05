<x-filament-panels::page>
    <form wire:submit.prevent="syncOrcid">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap gap-3">
            <x-filament::button type="button" wire:click="syncOrcid" color="primary">
                Sync from ORCID
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
        <h3 class="text-lg font-semibold">Citation Stats (Google Scholar)</h3>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Google Scholar does not provide a public API. Update citation totals manually in
            <strong>Site Content → Profile</strong> under Citation Stats.
        </p>
    </div>
</x-filament-panels::page>
