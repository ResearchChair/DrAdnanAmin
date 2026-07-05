<?php

namespace App\Http\Controllers;

use App\Support\CvAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CvDownloadController extends Controller
{
    public function show(Request $request): View|StreamedResponse|RedirectResponse
    {
        $profile = CvAccess::profile();

        abort_unless($profile, 404);

        if ($request->filled('key') && CvAccess::keyIsValid($request->query('key'))) {
            return CvAccess::downloadResponse($profile);
        }

        if (! CvAccess::requiresKey()) {
            return CvAccess::downloadResponse($profile);
        }

        return view('cv', [
            'profile' => $profile,
            'error' => $request->filled('key') ? 'Invalid download key. Please try again.' : null,
        ]);
    }

    public function download(Request $request): StreamedResponse|RedirectResponse
    {
        $profile = CvAccess::profile();

        abort_unless($profile, 404);

        $request->validate([
            'key' => CvAccess::requiresKey() ? ['required', 'string'] : ['nullable', 'string'],
        ]);

        if (! CvAccess::keyIsValid($request->input('key'))) {
            return redirect()
                ->route('cv.show')
                ->withErrors(['key' => 'Invalid download key. Please try again.']);
        }

        return CvAccess::downloadResponse($profile);
    }
}
