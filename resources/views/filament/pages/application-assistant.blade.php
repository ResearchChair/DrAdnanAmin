<x-filament-panels::page>
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100">
        <strong>AI draft only.</strong> Verify every claim against your CV before submitting.
        Configure <code>OPENAI_API_KEY</code> and/or <code>GROQ_API_KEY</code> in <code>.env</code>. Use <strong>Auto</strong> to fail over when one provider hits quota or rate limits.
    </div>

    <form wire:submit.prevent="generate">
        {{ $this->form }}

        <div class="mt-6 flex flex-wrap gap-3">
            <x-filament::button type="button" wire:click="generate" color="primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="generate">Generate draft</span>
                <span wire:loading wire:target="generate">Generating…</span>
            </x-filament::button>

            <x-filament::button type="button" wire:click="saveDraft" color="gray" wire:loading.attr="disabled">
                Save edits
            </x-filament::button>

            <x-filament::button type="button" wire:click="downloadMarkdown" color="success" wire:loading.attr="disabled">
                Download Markdown
            </x-filament::button>

            <x-filament::button type="button" wire:click="downloadPdf" color="success" wire:loading.attr="disabled">
                Download PDF
            </x-filament::button>
        </div>
    </form>

    <div class="mt-10 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent drafts</h3>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Load a previous generation to edit or re-download.</p>

        @forelse($this->recentDrafts as $draft)
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                <div class="min-w-0">
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $draft->typeLabel() }}
                        @if($draft->institution)
                            <span class="text-gray-500">· {{ $draft->institution }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $draft->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                        · {{ $draft->providerLabel() }}@if($draft->model) / {{ $draft->model }}@endif
                        @if($draft->position_title)
                            · {{ \Illuminate\Support\Str::limit($draft->position_title, 60) }}
                        @endif
                    </div>
                </div>
                <x-filament::button type="button" size="sm" color="gray" wire:click="loadDraft({{ $draft->id }})">
                    Load
                </x-filament::button>
            </div>
        @empty
            <p class="mt-4 text-sm text-gray-500">No drafts yet.</p>
        @endforelse
    </div>
</x-filament-panels::page>
