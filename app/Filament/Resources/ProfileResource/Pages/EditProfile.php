<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use App\Models\Profile;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Profile $record) {
                    if (Profile::query()->count() <= 1) {
                        Notification::make()
                            ->title('Cannot delete the only profile')
                            ->body('Create another profile before deleting this one.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }

                    if ($record->is_active) {
                        Notification::make()
                            ->title('Cannot delete the active profile')
                            ->body('Disable this profile or activate another profile first.')
                            ->danger()
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('orcid_id') && $this->record->orcid_id) {
            Notification::make()
                ->title('Syncing publications from ORCID')
                ->body('Your ORCID works are being imported now. Check Research → Publications in a moment.')
                ->success()
                ->send();
        }
    }
}
