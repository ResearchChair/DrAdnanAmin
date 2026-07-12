<?php

namespace App\Services\Applications;

use App\Models\ConsultancyEngagement;
use App\Models\Profile;
use App\Models\Publication;
use App\Models\ResearchActivity;
use App\Models\SoftwareSolution;
use App\Models\Student;
use App\Models\TrainingSession;
use App\Models\WorkedWithOrganization;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApplicationContextService
{
    /**
     * @param  array<int>  $publicationIds
     * @param  array{
     *   include_scholars?: bool,
     *   include_activities?: bool,
     *   include_training?: bool,
     *   include_consultancy?: bool,
     *   include_software?: bool
     * }  $options
     */
    public function build(Profile $profile, array $publicationIds = [], array $options = []): string
    {
        $maxPubs = (int) config('llm.max_publications', config('openai.max_publications', 15));
        $publicationIds = array_slice(array_values(array_unique(array_map('intval', $publicationIds))), 0, $maxPubs);

        $sections = [];
        $sections[] = $this->candidateSection($profile);

        $pubs = $this->selectedPublications($publicationIds);
        $sections[] = $this->publicationsSection($pubs);

        if (! empty($options['include_scholars'])) {
            $sections[] = $this->scholarsSection();
        }

        if (! empty($options['include_activities'])) {
            $sections[] = $this->activitiesSection();
        }

        if (! empty($options['include_training'])) {
            $sections[] = $this->trainingSection();
        }

        if (! empty($options['include_consultancy'])) {
            $sections[] = $this->consultancySection();
        }

        if (! empty($options['include_software'])) {
            $sections[] = $this->softwareSection();
        }

        if (! empty($options['include_worked_with'])) {
            $sections[] = $this->workedWithSection();
        }

        return implode("\n\n", array_filter($sections));
    }

    protected function candidateSection(Profile $profile): string
    {
        $lines = [
            '## CANDIDATE',
            'Name: '.$profile->name,
            'Credentials: '.($profile->credentials ?: 'n/a'),
            'Title: '.($profile->title ?: 'n/a'),
            'Affiliation: '.($profile->affiliation ?: 'n/a'),
            'Secondary affiliation: '.($profile->secondary_affiliation ?: 'n/a'),
            'Email: '.($profile->email ?: 'n/a'),
            'Location: '.($profile->location ?: 'n/a'),
            'Tagline: '.($profile->tagline ?: 'n/a'),
            'Research interests: '.($profile->research_interests ?: 'n/a'),
        ];

        $highlights = $profile->flyerHighlightsList();
        if ($highlights !== []) {
            $lines[] = 'Highlights:';
            foreach ($highlights as $item) {
                $lines[] = '- '.$item;
            }
        }

        $bio = trim(Str::of(strip_tags((string) $profile->bio_html))->squish());
        if ($bio !== '') {
            $lines[] = 'Biography (plain text):';
            $lines[] = Str::limit($bio, 2500);
        }

        return implode("\n", $lines);
    }

    /**
     * @param  Collection<int, Publication>  $publications
     */
    protected function publicationsSection(Collection $publications): string
    {
        if ($publications->isEmpty()) {
            return "## SELECTED PUBLICATIONS\nNone selected. Rely only on candidate interests and biography; do not invent publications.";
        }

        $lines = ['## SELECTED PUBLICATIONS'];
        foreach ($publications->values() as $index => $publication) {
            $n = $index + 1;
            $lines[] = "{$n}. ".$publication->toShortCitation();
            $lines[] = '   Type: '.$publication->type_label.'; Venue: '.($publication->venue ?: 'n/a');
            if (filled($publication->abstract)) {
                $lines[] = '   Abstract: '.Str::limit(strip_tags((string) $publication->abstract), 400);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<int>  $publicationIds
     * @return Collection<int, Publication>
     */
    protected function selectedPublications(array $publicationIds): Collection
    {
        if ($publicationIds === []) {
            return collect();
        }

        $pubs = Publication::query()
            ->visible()
            ->whereIn('id', $publicationIds)
            ->get()
            ->keyBy('id');

        return collect($publicationIds)
            ->map(fn (int $id) => $pubs->get($id))
            ->filter()
            ->values();
    }

    protected function scholarsSection(): string
    {
        $students = Student::query()
            ->visible()
            ->ordered()
            ->limit(20)
            ->get(['name', 'status', 'degree', 'batch', 'thesis_title', 'completion_year']);

        if ($students->isEmpty()) {
            return '';
        }

        $lines = ['## SUPERVISION / SCHOLARS (summary)'];
        foreach ($students as $student) {
            $bits = array_filter([
                $student->name,
                $student->degree,
                $student->status,
                $student->batch ? 'batch '.$student->batch : null,
                $student->completion_year ? 'completed '.$student->completion_year : null,
                $student->thesis_title ? 'thesis: '.Str::limit($student->thesis_title, 120) : null,
            ]);
            $lines[] = '- '.implode(' | ', $bits);
        }

        return implode("\n", $lines);
    }

    protected function activitiesSection(): string
    {
        $activities = ResearchActivity::query()
            ->visible()
            ->orderBy('sort_order')
            ->limit(25)
            ->get(['type', 'title', 'organization', 'year']);

        if ($activities->isEmpty()) {
            return '';
        }

        $lines = ['## RESEARCH SERVICE / ACTIVITIES'];
        foreach ($activities as $activity) {
            $lines[] = '- '.implode(' | ', array_filter([
                $activity->type,
                $activity->title,
                $activity->organization,
                $activity->year,
            ]));
        }

        return implode("\n", $lines);
    }

    protected function trainingSection(): string
    {
        $sessions = TrainingSession::query()
            ->visible()
            ->orderByDesc('year')
            ->limit(15)
            ->get(['title', 'type', 'year', 'organization', 'event_name', 'location']);

        if ($sessions->isEmpty()) {
            return '';
        }

        $lines = ['## TRAINING / FACILITATION'];
        foreach ($sessions as $session) {
            $lines[] = '- '.implode(' | ', array_filter([
                $session->title,
                $session->type,
                $session->year,
                $session->event_name,
                $session->organization,
                $session->location,
            ]));
        }

        return implode("\n", $lines);
    }

    protected function consultancySection(): string
    {
        $items = ConsultancyEngagement::query()
            ->visible()
            ->ordered()
            ->limit(20)
            ->get();

        if ($items->isEmpty()) {
            return '';
        }

        $lines = ['## CONSULTANCY ENGAGEMENTS'];
        foreach ($items as $item) {
            $lines[] = '- '.implode(' | ', array_filter([
                $item->title,
                $item->organization,
                $item->type_label,
                $item->role,
                $item->yearRangeLabel() ?: null,
                $item->location,
            ]));
            if (filled($item->description)) {
                $lines[] = '  Summary: '.Str::limit(strip_tags((string) $item->description), 220);
            }
        }

        return implode("\n", $lines);
    }

    protected function softwareSection(): string
    {
        $items = SoftwareSolution::query()
            ->visible()
            ->ordered()
            ->limit(20)
            ->get();

        if ($items->isEmpty()) {
            return '';
        }

        $lines = ['## SOFTWARE SOLUTIONS DEVELOPED'];
        foreach ($items as $item) {
            $lines[] = '- '.implode(' | ', array_filter([
                $item->name,
                $item->organization,
                $item->type_label,
                $item->year,
                $item->tech_stack,
            ]));
            if (filled($item->tagline)) {
                $lines[] = '  Tagline: '.$item->tagline;
            }
            if (filled($item->description)) {
                $lines[] = '  Summary: '.Str::limit(strip_tags((string) $item->description), 220);
            }
        }

        return implode("\n", $lines);
    }

    protected function workedWithSection(): string
    {
        $items = WorkedWithOrganization::query()
            ->visible()
            ->ordered()
            ->limit(30)
            ->get(['name']);

        if ($items->isEmpty()) {
            return '';
        }

        $lines = ['## ORGANIZATIONS WORKED WITH'];
        foreach ($items as $item) {
            $lines[] = '- '.$item->name;
        }

        return implode("\n", $lines);
    }
}
