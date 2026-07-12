<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Js;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getRedirectUrl(): string
    {
        return PublicationResource::getUrl('index');
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
            ->alpineClickHandler('window.location.href = '.Js::from(PublicationResource::getUrl('index')))
            ->color('gray');
    }
}
