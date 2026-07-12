<?php

namespace App\Services\Applications;

use App\Models\ApplicationDraft;
use App\Models\Profile;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class ApplicationDraftService
{
    public function __construct(
        protected LlmClient $llm,
        protected ApplicationContextService $contextService,
    ) {}

    /**
     * @param  array{
     *   document_type: string,
     *   job_text: string,
     *   position_title?: string|null,
     *   institution?: string|null,
     *   tone?: string|null,
     *   extra_notes?: string|null,
     *   publication_ids?: array<int>,
     *   include_scholars?: bool,
     *   include_activities?: bool,
     *   include_training?: bool,
     *   include_consultancy?: bool,
     *   include_software?: bool,
     *   llm_provider?: string|null,
     *   user_id?: int|null,
     * }  $input
     */
    public function generate(array $input): ApplicationDraft
    {
        $this->assertRateLimit($input['user_id'] ?? null);

        $documentType = $input['document_type'];
        if (! isset(ApplicationDraft::TYPES[$documentType])) {
            throw new RuntimeException('Invalid document type.');
        }

        $jobText = trim((string) ($input['job_text'] ?? ''));
        if ($jobText === '') {
            throw new RuntimeException('Paste the job posting, postdoc call, or research call text.');
        }

        $publicationIds = array_values(array_unique(array_map('intval', $input['publication_ids'] ?? [])));
        $maxPubs = (int) config('llm.max_publications', 15);
        if (count($publicationIds) > $maxPubs) {
            throw new RuntimeException("Select at most {$maxPubs} publications.");
        }

        $profile = Profile::current();
        $options = [
            'include_scholars' => (bool) ($input['include_scholars'] ?? true),
            'include_activities' => (bool) ($input['include_activities'] ?? true),
            'include_training' => (bool) ($input['include_training'] ?? false),
            'include_consultancy' => (bool) ($input['include_consultancy'] ?? true),
            'include_software' => (bool) ($input['include_software'] ?? true),
            'include_worked_with' => (bool) ($input['include_worked_with'] ?? true),
            'llm_provider_request' => $input['llm_provider'] ?? LlmClient::PROVIDER_AUTO,
        ];

        $context = $this->contextService->build($profile, $publicationIds, $options);
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $this->userPrompt($documentType, $input, $context, $jobText)],
        ];

        $provider = (string) ($input['llm_provider'] ?? config('llm.default', LlmClient::PROVIDER_AUTO));
        $result = $this->llm->chat($messages, $provider);

        $options['attempted_providers'] = $result['attempted_providers'] ?? [];
        $options['failover'] = (bool) ($result['failover'] ?? false);

        $draft = ApplicationDraft::query()->create([
            'user_id' => $input['user_id'] ?? null,
            'document_type' => $documentType,
            'position_title' => $input['position_title'] ?? null,
            'institution' => $input['institution'] ?? null,
            'tone' => $input['tone'] ?? 'formal',
            'job_text' => $jobText,
            'extra_notes' => $input['extra_notes'] ?? null,
            'publication_ids' => $publicationIds,
            'options' => $options,
            'output_markdown' => $result['content'],
            'model' => $result['model'],
            'provider' => $result['provider'] ?? null,
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
        ]);

        $this->hitRateLimit($input['user_id'] ?? null);

        return $draft;
    }

    public function updateOutput(ApplicationDraft $draft, string $markdown): ApplicationDraft
    {
        $draft->update(['output_markdown' => $markdown]);

        return $draft->fresh();
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert academic career writing assistant for faculty and researchers.
Write clear, professional application materials grounded ONLY in the provided candidate context and the job/call text.

Hard rules:
- Never invent publications, degrees, grants, affiliations, awards, metrics, or employment history.
- If a fact is missing from context, omit it or phrase cautiously ("experience includes…") without fabricating details.
- Prefer the selected publications list; do not add other papers unless they appear in context.
- Match honest terminology from the call when the candidate's record supports it.
- Output Markdown only (no code fences unless the user asked for code).
- Do not include a preamble like "Here is your draft".
PROMPT;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function userPrompt(string $documentType, array $input, string $context, string $jobText): string
    {
        $label = ApplicationDraft::TYPES[$documentType];
        $tone = $input['tone'] ?? 'formal';
        $position = trim((string) ($input['position_title'] ?? '')) ?: 'n/a';
        $institution = trim((string) ($input['institution'] ?? '')) ?: 'n/a';
        $notes = trim((string) ($input['extra_notes'] ?? '')) ?: 'none';

        $instructions = match ($documentType) {
            ApplicationDraft::TYPE_COVER_LETTER => <<<'TXT'
Write a cover letter in Markdown.
Structure: greeting, 3–5 short paragraphs (fit to role, research alignment, selected contributions, closing), sign-off with candidate name.
Keep it concise unless notes ask for longer.
TXT,
            ApplicationDraft::TYPE_RESEARCH_PROPOSAL => <<<'TXT'
Write a short research proposal draft in Markdown (~1–2 pages of text).
Required sections:
1. Title
2. Background & motivation
3. Objectives
4. Methods / approach
5. Expected outcomes & impact
6. Fit with the host / call
Ground methods in the candidate's demonstrated expertise from context. Do not invent prior funding.
TXT,
            ApplicationDraft::TYPE_TAILORED_CV => <<<'TXT'
Write a tailored academic CV narrative in Markdown (not a graphical layout).
Sections:
- Professional summary (targeted to this call)
- Core research themes
- Selected publications (use the numbered list from context; keep citations intact)
- Supervision & mentoring (if present in context)
- Professional service / training (if present)
- Consultancy engagements (if present)
- Software solutions developed (if present)
- Closing note on fit
This complements a formal PDF CV; focus on prioritization and narrative for this application.
TXT,
            default => 'Write the requested document in Markdown.',
        };

        return <<<PROMPT
Document type: {$label}
Tone: {$tone}
Position title: {$position}
Institution / host: {$institution}
Extra notes from candidate: {$notes}

{$instructions}

--- CANDIDATE CONTEXT ---
{$context}

--- JOB / POSTDOC / RESEARCH CALL TEXT ---
{$jobText}
PROMPT;
    }

    protected function assertRateLimit(?int $userId): void
    {
        $limit = (int) config('llm.rate_limit_per_hour', 20);
        $key = $this->rateLimitKey($userId);
        $count = (int) Cache::get($key, 0);
        if ($count >= $limit) {
            throw new RuntimeException("Hourly generation limit reached ({$limit}). Try again later.");
        }
    }

    protected function hitRateLimit(?int $userId): void
    {
        $key = $this->rateLimitKey($userId);
        if (! Cache::has($key)) {
            Cache::put($key, 1, now()->addHour());

            return;
        }

        Cache::increment($key);
    }

    protected function rateLimitKey(?int $userId): string
    {
        return 'llm-app-drafts:'.($userId ?? 'guest').':'.now()->format('YmdH');
    }
}
