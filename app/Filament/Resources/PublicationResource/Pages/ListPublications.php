<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use App\Mail\CollaboratorPublicationInviteMail;
use App\Models\Profile;
use App\Models\PublicationCollaborator;
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
                    Select::make('email')
                        ->label('Co-author email')
                        ->options(fn () => PublicationCollaborator::query()
                            ->select('email')
                            ->distinct()
                            ->orderBy('email')
                            ->pluck('email', 'email')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
                    $collaborators = PublicationCollaborator::query()
                        ->where('email', $email)
                        ->get();

                    if ($email === '' || $collaborators->isEmpty()) {
                        Notification::make()
                            ->title('Co-author email not found')
                            ->body('Choose an email that is already attached to at least one publication.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $plainToken = Str::random(64);
                    $tokenHash = hash('sha256', $plainToken);

                    foreach ($collaborators as $collaborator) {
                        $collaborator->forceFill([
                            'token_hash' => $tokenHash,
                            'expires_at' => null,
                            'last_sent_at' => now(),
                        ])->save();
                    }

                    $accessUrl = URL::signedRoute('publications.collaborator', [
                        'email' => $email,
                        'token' => $plainToken,
                    ]);

                    try {
                        Mail::to($email)
                            ->send(new CollaboratorPublicationInviteMail($accessUrl));

                        Notification::make()
                            ->title('Co-author link sent')
                            ->body('The co-author can use the link to view all matched publications.')
                            ->success()
                            ->send();
                    } catch (\Throwable) {
                        Notification::make()
                            ->title('Unable to send link')
                            ->body('Please verify mail settings and try again.')
                            ->danger()
                            ->send();
                    }
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
