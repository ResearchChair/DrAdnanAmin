<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use App\Models\Profile;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! Profile::query()->exists()) {
            $data['is_active'] = true;
        } elseif (! array_key_exists('is_active', $data)) {
            $data['is_active'] = false;
        }

        return $data;
    }
}
