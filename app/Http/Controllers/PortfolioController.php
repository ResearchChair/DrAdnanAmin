<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Profile;
use App\Models\Publication;
use App\Models\ResearchActivity;
use App\Models\ShowcaseProduct;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Support\SocialEmbed;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    protected function sharedData(): array
    {
        $profile = Profile::current()->loadMissing([
            'citationStats',
            'academicProfiles' => fn ($q) => $q->where('is_visible', true),
            'socialLinks' => fn ($q) => $q->where('is_visible', true),
        ]);

        return [
            'profile' => $profile,
            'stats' => $profile->citationStats,
            'academicProfiles' => $profile->academicProfiles,
            'socialLinks' => $profile->socialLinks,
            'accentColor' => SiteSetting::get('accent_color', '#5B2C6F'),
            'secondaryColor' => SiteSetting::get('secondary_color', '#C17AA8'),
            'surfaceColor' => SiteSetting::get('surface_color', '#FFF9F5'),
            'surfaceMutedColor' => SiteSetting::get('surface_muted_color', '#F5EBE8'),
            'metaDescription' => SiteSetting::get('meta_description'),
        ];
    }

    public function home(): View
    {
        $data = $this->sharedData();
        $data['recentPublications'] = Publication::query()
            ->visible()
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->limit(5)
            ->get();
        $data['showcaseProducts'] = ShowcaseProduct::query()
            ->visible()
            ->orderBy('sort_order')
            ->get();

        $youtubePageUrl = SiteSetting::get('youtube_channel_url')
            ?: $data['academicProfiles']->firstWhere('platform', 'youtube')?->url;
        $facebookPageUrl = SiteSetting::get('facebook_page_url')
            ?: $data['socialLinks']->firstWhere('platform', 'facebook')?->url;

        $data['youtubeEmbedSrc'] = SocialEmbed::resolveHomeYoutubeEmbed(
            $youtubePageUrl,
            SiteSetting::get('youtube_embed_url'),
            SiteSetting::get('youtube_channel_id'),
        );
        $data['facebookEmbedSrc'] = SocialEmbed::facebookEmbedSrc($facebookPageUrl);
        $data['youtubePageUrl'] = SocialEmbed::youtubePageUrl($youtubePageUrl);
        $data['youtubeAutoplay'] = SocialEmbed::youtubeAutoplayEnabled();
        $data['youtubeDailyRotation'] = SocialEmbed::youtubeDailyRotationEnabled();
        $data['facebookPageUrl'] = SocialEmbed::facebookPageUrl($facebookPageUrl);

        $data['featuredGalleryImages'] = GalleryImage::query()
            ->where('is_featured', true)
            ->whereHas('album', fn ($q) => $q->where('is_visible', true))
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        if ($data['featuredGalleryImages']->isEmpty()) {
            $data['featuredGalleryImages'] = GalleryImage::query()
                ->whereHas('album', fn ($q) => $q->where('is_visible', true))
                ->orderBy('sort_order')
                ->limit(4)
                ->get();
        }

        return view('home', $data);
    }

    public function about(): View
    {
        return view('about', $this->sharedData());
    }

    public function publications(Request $request): View
    {
        $data = $this->sharedData();
        $data['types'] = config('academic.publication_types');
        $data['currentType'] = $request->string('type')->toString();
        $data['search'] = $request->string('q')->toString();

        $query = Publication::query()->visible()->orderByDesc('year')->orderBy('sort_order');

        if ($data['currentType']) {
            $query->where('type', $data['currentType']);
        }

        if ($data['search']) {
            $query->where(function ($q) use ($data) {
                $q->where('title', 'like', '%'.$data['search'].'%')
                    ->orWhere('authors', 'like', '%'.$data['search'].'%')
                    ->orWhere('venue', 'like', '%'.$data['search'].'%');
            });
        }

        $data['publications'] = $query->paginate(15)->withQueryString();

        return view('publications.index', $data);
    }

    public function research(): View
    {
        $data = $this->sharedData();
        $data['activities'] = ResearchActivity::query()->visible()->orderBy('sort_order')->get()->groupBy('type');

        return view('research', $data);
    }

    public function students(): View
    {
        $data = $this->sharedData();
        $data['inProgress'] = Student::query()->visible()->status('in_progress')->orderBy('sort_order')->get();
        $data['completed'] = Student::query()->visible()->status('completed')->orderByDesc('completion_year')->get();

        return view('students', $data);
    }

    public function training(): View
    {
        $data = $this->sharedData();
        $data['sessions'] = TrainingSession::query()->visible()->orderByDesc('year')->orderBy('sort_order')->get();

        return view('training', $data);
    }

    public function gallery(): View
    {
        $data = $this->sharedData();
        $data['albums'] = GalleryAlbum::query()->where('is_visible', true)->with('images')->orderBy('sort_order')->get();

        return view('gallery', $data);
    }

    public function contact(): View
    {
        $data = $this->sharedData();
        $data['contactMessage'] = SiteSetting::get('contact_message');

        return view('contact', $data);
    }
}
