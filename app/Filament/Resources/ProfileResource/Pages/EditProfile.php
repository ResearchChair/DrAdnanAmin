<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

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
