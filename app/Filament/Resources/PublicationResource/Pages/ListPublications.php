<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use App\Mail\CollaboratorPublicationInviteMail;
use App\Models\Profile;
use App\Models\Publication;
use App\Services\PublicationSyncService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
            Actions\Action::make('sendCollaboratorLinks')
                ->label('Send co-author links')
                ->icon('heroicon-o-paper-airplane')
                ->button()
                ->color('primary')
                ->form([
                    Select::make('publication_id')
                        ->label('Publication')
                        ->options(fn () => Publication::query()
                            ->orderByDesc('year')
                            ->orderBy('title')
                            ->pluck('title', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $publication = Publication::query()
                        ->with('collaborators')
                        ->find($data['publication_id'] ?? null);

                    if (! $publication) {
                        Notification::make()
                            ->title('Publication not found')
                            ->warning()
                            ->send();

                        return;
                    }

                    if ($publication->collaborators->isEmpty()) {
                        Notification::make()
                            ->title('No co-author emails found')
                            ->body('Add at least one collaborator email before sending links.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $sent = 0;
                    $failed = 0;

                    foreach ($publication->collaborators as $collaborator) {
                        try {
                            $plainToken = Str::random(64);

                            $collaborator->forceFill([
                                'token_hash' => hash('sha256', $plainToken),
                                'expires_at' => null,
                                'last_sent_at' => now(),
                            ])->save();

                            $accessUrl = URL::signedRoute('publications.collaborator', [
                                'collaborator' => $collaborator->id,
                                'token' => $plainToken,
                            ]);

                            Mail::to($collaborator->email)
                                ->send(new CollaboratorPublicationInviteMail($publication, $accessUrl));

                            $sent++;
                        } catch (\Throwable) {
                            $failed++;
                        }
                    }

                    if ($sent > 0) {
                        Notification::make()
                            ->title('Co-author links sent')
                            ->body('Sent '.$sent.' invite link(s).'.($failed > 0 ? ' '.$failed.' failed.' : ''))
                            ->success()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Unable to send links')
                        ->body('Please verify mail settings and try again.')
                        ->danger()
                        ->send();
                }),
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
