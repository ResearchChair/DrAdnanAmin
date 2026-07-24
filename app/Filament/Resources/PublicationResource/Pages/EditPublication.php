<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Mail\CollaboratorPublicationInviteMail;
use App\Filament\Resources\PublicationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Js;
use Illuminate\Support\Str;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendCollaboratorLinks')
                ->label('Send co-author links')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->color('primary')
                ->action(function (): void {
                    $publication = $this->record->loadMissing('collaborators');

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
                            $expiresAt = now()->addDays(max(1, (int) config('academic.collaborator_link_expiry_days', 14)));
                            $plainToken = Str::random(64);

                            $collaborator->forceFill([
                                'token_hash' => hash('sha256', $plainToken),
                                'expires_at' => $expiresAt,
                                'last_sent_at' => now(),
                            ])->save();

                            $accessUrl = URL::temporarySignedRoute(
                                'publications.collaborator',
                                $expiresAt,
                                [
                                    'collaborator' => $collaborator->id,
                                    'token' => $plainToken,
                                ]
                            );

                            Mail::to($collaborator->email)
                                ->send(new CollaboratorPublicationInviteMail($publication, $accessUrl, $expiresAt));

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
            DeleteAction::make()
                ->successRedirectUrl(PublicationResource::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PublicationResource::getUrl('index');
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
            ->alpineClickHandler('window.location.href = '.Js::from(PublicationResource::getUrl('index')))
            ->color('gray');
    }
}
