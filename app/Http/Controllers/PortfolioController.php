<?php

namespace App\Http\Controllers;

use App\Models\EarnedBadge;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Profile;
use App\Models\Publication;
use App\Models\ResearchActivity;
use App\Models\ShowcaseProduct;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Support\PublicationSummary;
use App\Support\SocialEmbed;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $data['earnedBadges'] = EarnedBadge::query()
            ->visible()
            ->orderBy('sort_order')
            ->orderBy('title')
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

        $all = Publication::query()
            ->visible()
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $search = $request->string('q')->toString();
        if ($search !== '') {
            $all = $all->filter(function (Publication $publication) use ($search) {
                $haystack = Str::lower(($publication->title ?? '').' '.($publication->authors ?? '').' '.($publication->venue ?? ''));

                return Str::contains($haystack, Str::lower($search));
            })->values();
        }

        $data['search'] = $search;
        $data['journalPublications'] = $all->where('type', 'journal')->values();
        $data['conferencePublications'] = $all->where('type', 'conference')->values();
        $data['bookChapterPublications'] = $all->whereIn('type', ['book_chapter', 'book'])->values();
        $data['inProgressPublications'] = $all->where('type', 'in_progress')->values();
        $data['publicationSummary'] = PublicationSummary::build(
            $all,
            $data['stats']?->total_citations !== null ? (int) $data['stats']->total_citations : null,
        );
        $data['defaultPublicationTab'] = match (true) {
            $data['journalPublications']->isNotEmpty() => 'journals',
            $data['conferencePublications']->isNotEmpty() => 'conferences',
            $data['bookChapterPublications']->isNotEmpty() => 'book_chapters',
            $data['inProgressPublications']->isNotEmpty() => 'in_progress',
            default => 'summary',
        };

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

        $statuses = config('academic.student_statuses', []);
        $students = Student::query()
            ->visible()
            ->with(['publications' => fn ($query) => $query->orderByDesc('year')->orderBy('title')])
            ->ordered()
            ->get();

        $studentsByStatus = collect($statuses)
            ->mapWithKeys(fn (string $label, string $status): array => [
                $status => $students->where('status', $status)->values(),
            ]);

        $data['studentStatuses'] = $statuses;
        $data['studentsByStatus'] = $studentsByStatus;
        $data['defaultStudentTab'] = collect($statuses)
            ->keys()
            ->first(fn (string $status): bool => $studentsByStatus[$status]->isNotEmpty()) ?? 'completed';

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
