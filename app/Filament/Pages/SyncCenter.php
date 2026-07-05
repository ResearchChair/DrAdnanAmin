<?php

namespace App\Filament\Pages;

use App\Services\BibTeXImportService;
use App\Services\PublicationSyncService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SyncCenter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Sync';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.sync-center';

    public ?string $orcid_id = null;

    public ?array $bibtex_file = null;

    public function mount(): void
    {
        $profile = \App\Models\Profile::query()->first();
        $this->orcid_id = $profile?->orcid_id ?: config('academic.orcid_id');
        $this->form->fill(['orcid_id' => $this->orcid_id]);
    }

    public function getLastSyncedLabel(): ?string
    {
        $syncedAt = \App\Models\Profile::query()->value('orcid_synced_at');

        return $syncedAt ? $syncedAt->timezone(config('app.timezone'))->format('M j, Y g:i A') : null;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('ORCID Sync')
                ->description('Publications are imported automatically from ORCID when you save your ORCID ID on the profile, and refreshed daily. Use Sync below for an immediate update.')
                ->schema([
                    TextInput::make('orcid_id')
                        ->label('ORCID ID')
                        ->placeholder('0000-0000-0000-0000')
                        ->helperText('Also editable under Site Content → Profile → Academic IDs.'),
                ]),
            Section::make('BibTeX Import')->schema([
                FileUpload::make('bibtex_file')
                    ->label('BibTeX File')
                    ->acceptedFileTypes(['text/plain', 'application/x-bibtex', '.bib'])
                    ->maxFiles(1),
            ]),
        ]);
    }

    public function syncOrcid(PublicationSyncService $syncService): void
    {
        $result = $syncService->runOrcidSync($this->orcid_id);

        if (! empty($result['errors'])) {
            Notification::make()
                ->title('ORCID sync failed')
                ->body(implode(' ', $result['errors']))
                ->danger()
                ->send();

            return;
        }

        $body = "Added: {$result['added']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}";

        if (isset($result['enriched'])) {
            $body .= ". OpenAlex enriched: {$result['enriched']}";
        }

        Notification::make()
            ->title('ORCID sync completed')
            ->body($body)
            ->success()
            ->send();
    }

    public function enrichOpenAlex(PublicationSyncService $syncService): void
    {
        $result = $syncService->enrichAll();

        Notification::make()
            ->title('OpenAlex enrichment completed')
            ->body("Enriched: {$result['enriched']}, Failed: {$result['failed']}")
            ->success()
            ->send();
    }

    public function importBibtex(BibTeXImportService $importer): void
    {
        if (empty($this->bibtex_file)) {
            Notification::make()->title('Please upload a BibTeX file')->danger()->send();

            return;
        }

        $path = storage_path('app/public/'.array_values($this->bibtex_file)[0]);
        $contents = file_get_contents($path);
        $result = $importer->import($contents);

        Notification::make()
            ->title('BibTeX import completed')
            ->body("Added: {$result['added']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}")
            ->success()
            ->send();
    }
}
