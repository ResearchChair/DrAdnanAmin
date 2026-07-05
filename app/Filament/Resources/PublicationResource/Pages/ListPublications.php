<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use App\Models\Profile;
use App\Services\PublicationSyncService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    public function mount(): void
    {
        parent::mount();

        $profile = Profile::query()->first();

        if (! $profile?->orcid_id || $profile->orcid_synced_at || session('orcid_publications_synced')) {
            return;
        }

        session(['orcid_publications_synced' => true]);

        $result = app(PublicationSyncService::class)->runOrcidSync($profile->orcid_id);

        if (empty($result['errors'])) {
            Notification::make()
                ->title('Publications imported from ORCID')
                ->body("Added {$result['added']}, updated {$result['updated']}.")
                ->success()
                ->send();
        }
    }

    public function getSubheading(): ?string
    {
        return 'Publications sync from ORCID automatically. You can still add or edit entries manually when needed.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncOrcid')
                ->label('Sync from ORCID')
                ->icon('heroicon-o-arrow-path')
                ->action(function (PublicationSyncService $syncService) {
                    $profile = Profile::query()->first();
                    $orcidId = $profile?->orcid_id ?: config('academic.orcid_id');

                    if (! $orcidId) {
                        Notification::make()
                            ->title('ORCID ID required')
                            ->body('Add your ORCID ID under Site Content → Profile first.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $result = $syncService->runOrcidSync($orcidId);

                    if (! empty($result['errors'])) {
                        Notification::make()
                            ->title('ORCID sync failed')
                            ->body(implode(' ', $result['errors']))
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('ORCID sync completed')
                        ->body("Added: {$result['added']}, Updated: {$result['updated']}, Skipped: {$result['skipped']}")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make()
                ->label('Add manually'),
        ];
    }
}
