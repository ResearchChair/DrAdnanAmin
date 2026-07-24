<?php

namespace App\Http\Controllers;

use App\Models\ConsultancyEngagement;
use App\Models\EarnedBadge;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\Profile;
use App\Models\Publication;
use App\Models\PublicationCollaborator;
use App\Models\ResearchActivity;
use App\Models\ShowcaseProduct;
use App\Models\SiteSetting;
use App\Models\SoftwareSolution;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\WorkedWithOrganization;
use App\Support\PublicationSummary;
use App\Support\Seo;
use App\Support\SocialEmbed;
use App\Services\VisitorAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    protected function sharedData(array $seoOverrides = []): array
    {
        $profile = Profile::current()->loadMissing([
            'citationStats',
            'academicProfiles' => fn ($q) => $q->where('is_visible', true),
            'socialLinks' => fn ($q) => $q->where('is_visible', true),
        ]);

        $data = [
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

        return Seo::mergeInto($data, $seoOverrides);
    }

    public function home(): View
    {
        $profileName = Profile::current()->name;
        $data = $this->sharedData([
            'title' => $profileName.' | Home',
            'description' => SiteSetting::get('meta_description')
                ?: 'Academic portfolio of '.$profileName.' — research publications, scholars, teaching, and professional services.',
            'canonical' => route('home'),
            'type' => 'website',
        ]);
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
        $data['workedWithOrganizations'] = WorkedWithOrganization::query()
            ->visible()
            ->ordered()
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
        $data = $this->sharedData();
        $profile = $data['profile'];

        return view('about', Seo::mergeInto($data, [
            'title' => 'Biography | '.$profile->name,
            'description' => Str::limit(
                trim(strip_tags((string) $profile->bio_html)) ?: ('Biography and academic profile of '.$profile->name),
                160,
                ''
            ),
            'canonical' => route('about'),
        ]));
    }

    public function publications(Request $request): View
    {
        $name = $this->profileName();
        $data = $this->sharedData([
            'title' => 'Publications | '.$name,
            'description' => 'Research publications including journal articles, conference papers, and book chapters by '.$name.'.',
            'canonical' => route('publications'),
        ]);

        $all = Publication::query()
            ->visible()
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $search = $request->string('q')->toString();
        if ($search !== '') {
            // Comma/semicolon-separated topics: match any term (OR) across title, authors, venue
            $terms = collect(preg_split('/[,;]+/', $search))
                ->map(fn (string $term): string => Str::lower(trim($term)))
                ->filter()
                ->values();

            $all = $all->filter(function (Publication $publication) use ($terms) {
                $haystack = Str::lower(($publication->title ?? '').' '.($publication->authors ?? '').' '.($publication->venue ?? ''));

                return $terms->contains(fn (string $term): bool => Str::contains($haystack, $term));
            })->values();
        }

        $data['search'] = $search;
        $data['journalPublications'] = $all->where('type', 'journal')->values();
        $data['conferencePublications'] = $all->where('type', 'conference')->values();
        $data['bookChapterPublications'] = $all->whereIn('type', ['book_chapter', 'book'])->values();
        $data['inProgressPublications'] = $all
            ->filter(fn (Publication $publication): bool => (string) $publication->status !== 'published')
            ->values();
        $data['recommendablePublications'] = $all
            ->whereIn('type', ['journal', 'conference', 'book', 'book_chapter'])
            ->values();
        $data['publicationSummary'] = PublicationSummary::build(
            $all,
            $data['stats']?->total_citations !== null ? (int) $data['stats']->total_citations : null,
        );

        $requestedTab = $request->string('tab')->toString();
        $allowedTabs = ['journals', 'conferences', 'book_chapters', 'in_progress', 'summary', 'recommend'];
        $data['defaultPublicationTab'] = in_array($requestedTab, $allowedTabs, true)
            ? $requestedTab
            : match (true) {
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
        $data = $this->sharedData([
            'title' => 'Research | '.$this->profileName(),
            'description' => 'Research service, editorial roles, reviewing, and professional academic activities by '.$this->profileName().'.',
            'canonical' => route('research'),
        ]);
        $data['activities'] = ResearchActivity::query()->visible()->orderBy('sort_order')->get()->groupBy('type');

        return view('research', $data);
    }

    public function students(): View
    {
        $data = $this->sharedData([
            'title' => 'Scholars | '.$this->profileName(),
            'description' => 'Research scholars supervised by '.$this->profileName().' — completed, in progress, guest scholars, and FYP projects.',
            'canonical' => route('students'),
        ]);

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
        $data = $this->sharedData([
            'title' => 'Training & Facilitation | '.$this->profileName(),
            'description' => 'Workshops, training programs, and facilitation delivered by '.$this->profileName().'.',
            'canonical' => route('training'),
        ]);
        $data['sessions'] = TrainingSession::query()
            ->visible()
            ->with(['galleryAlbum' => fn ($query) => $query->where('is_visible', true)])
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->get();

        return view('training', $data);
    }

    public function services(Request $request): View
    {
        $data = $this->sharedData([
            'title' => 'Services | '.$this->profileName(),
            'description' => 'Consultancy engagements and software solutions delivered by '.$this->profileName().' for academic and industry partners.',
            'canonical' => route('services'),
        ]);
        $data['consultancyEngagements'] = ConsultancyEngagement::query()->visible()->ordered()->get();
        $data['softwareSolutions'] = SoftwareSolution::query()->visible()->ordered()->get();

        $requested = $request->string('tab')->toString();
        $data['defaultServicesTab'] = match (true) {
            in_array($requested, ['consultancy', 'software'], true) => $requested,
            $data['consultancyEngagements']->isNotEmpty() => 'consultancy',
            $data['softwareSolutions']->isNotEmpty() => 'software',
            default => 'consultancy',
        };

        return view('services', $data);
    }

    public function gallery(): View
    {
        $data = $this->sharedData([
            'title' => 'Gallery | '.$this->profileName(),
            'description' => 'Photo gallery from conferences, workshops, and academic events featuring '.$this->profileName().'.',
            'canonical' => route('gallery'),
        ]);
        $data['albums'] = GalleryAlbum::query()->where('is_visible', true)->with('images')->orderBy('sort_order')->get();

        return view('gallery', $data);
    }

    public function collaboratorPublications(Request $request, PublicationCollaborator $collaborator): View
    {
        $token = $request->string('token')->toString();
        $expectedHash = (string) $collaborator->token_hash;
        $actualHash = hash('sha256', $token);

        if ($token === '' || $expectedHash === '' || ! hash_equals($expectedHash, $actualHash)) {
            abort(403, 'Invalid collaborator link.');
        }

        $name = $this->profileName();
        $data = $this->sharedData([
            'title' => 'Co-authored Publications | '.$name,
            'description' => 'Publication list shared with invited co-authors.',
            'canonical' => route('publications'),
        ]);

        $all = Publication::query()
            ->whereHas('collaborators', fn ($query) => $query->where('email', $collaborator->email))
            ->orderByDesc('year')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $data['coauthorPublications'] = $all;
        $data['collaboratorEmail'] = $collaborator->email;

        return view('publications.collaborator', $data);
    }

    public function contact(VisitorAnalyticsService $analytics): View
    {
        $data = $this->sharedData([
            'title' => 'Contact | '.$this->profileName(),
            'description' => 'Contact '.$this->profileName().' for research collaboration, supervision, consultancy, or speaking engagements.',
            'canonical' => route('contact'),
        ]);
        $data['contactMessage'] = SiteSetting::get('contact_message');
        $data['visitorStats'] = $analytics->summary();

        return view('contact', $data);
    }

    protected function profileName(): string
    {
        return Profile::current()->name;
    }
}
