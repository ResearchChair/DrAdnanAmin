<?php

namespace App\Observers;

use App\Jobs\SyncPublicationsFromOrcidJob;
use App\Models\Profile;

class ProfileObserver
{
    public function saved(Profile $profile): void
    {
        if (! $profile->orcid_id) {
            return;
        }

        if (! $profile->wasChanged('orcid_id') && ! $profile->wasRecentlyCreated) {
            return;
        }

        SyncPublicationsFromOrcidJob::dispatchSync($profile->orcid_id);
    }
}
