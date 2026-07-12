<?php

namespace App\Filament\Pages;

use App\Models\ApplicationDraft;
use App\Models\Publication;
use App\Services\Applications\ApplicationDraftService;
use App\Services\Applications\ApplicationPdfService;
use App\Services\Llm\LlmClient;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class ApplicationAssistant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Application Assistant';

    protected static ?string $title = 'Application Assistant';

    protected static string $view = 'filament.pages.application-assistant';

    public ?array $data = [];

    public ?int $currentDraftId = null;

    public function mount(): void
    {
        $defaultProvider = config('llm.default', LlmClient::PROVIDER_AUTO);
        $llm = app(LlmClient::class);
        $options = $llm->availableProviderOptions();
        if ($options !== [] && ! isset($options[$defaultProvider])) {
            $defaultProvider = array_key_first($options);
        }

        $this->form->fill([
            'document_type' => ApplicationDraft::TYPE_COVER_LETTER,
            'tone' => 'formal',
            'llm_provider' => $defaultProvider ?: LlmClient::PROVIDER_AUTO,
            'include_scholars' => true,
            'include_activities' => true,
            'include_training' => false,
            'include_consultancy' => true,
            'include_software' => true,
            'include_worked_with' => true,
            'publication_ids' => [],
            'job_text' => '',
            'output_markdown' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Application target')
                    ->description('Paste the postdoc, job, or research call. AI drafts use only your portfolio data plus this text — verify before submitting.')
                    ->schema([
                        Select::make('document_type')
                            ->label('Document type')
                            ->options(ApplicationDraft::TYPES)
                            ->required()
                            ->native(false),
                        Select::make('llm_provider')
                            ->label('LLM provider')
                            ->options(fn () => app(LlmClient::class)->availableProviderOptions())
                            ->required()
                            ->native(false)
                            ->helperText('Auto tries the next provider on quota, rate-limit, or outage. Manual choice still fails over if the primary is exhausted.'),
                        TextInput::make('position_title')
                            ->label('Position / call title')
                            ->maxLength(255),
                        TextInput::make('institution')
                            ->label('Institution / host')
                            ->maxLength(255),
                        Select::make('tone')
                            ->options([
                                'formal' => 'Formal',
                                'concise' => 'Concise',
                                'persuasive' => 'Persuasive',
                            ])
                            ->required()
                            ->native(false),
                        Textarea::make('job_text')
                            ->label('Job / call text')
                            ->rows(10)
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Paste the full announcement or the most relevant sections.'),
                        Textarea::make('extra_notes')
                            ->label('Extra notes for the model')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('e.g. Emphasize graph ML; keep under 400 words; address PI by name.'),
                    ])
                    ->columns(2),

                Section::make('Portfolio context')
                    ->schema([
                        CheckboxList::make('publication_ids')
                            ->label('Publications to highlight (max '.config('llm.max_publications', 15).')')
                            ->options(fn () => Publication::query()
                                ->visible()
                                ->whereIn('type', ['journal', 'conference', 'book', 'book_chapter'])
                                ->orderByDesc('year')
                                ->orderBy('title')
                                ->get()
                                ->mapWithKeys(fn (Publication $p) => [
                                    $p->id => trim(($p->year ? "[{$p->year}] " : '').$p->title.' ('.$p->type_label.')'),
                                ])
                                ->all())
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(1)
                            ->helperText('Tip: search topics on the public Recommend tab first, then select matching titles here.'),
                        Toggle::make('include_scholars')->label('Include scholars / supervision summary'),
                        Toggle::make('include_activities')->label('Include research service activities'),
                        Toggle::make('include_training')->label('Include training / facilitation'),
                        Toggle::make('include_consultancy')->label('Include consultancy engagements'),
                        Toggle::make('include_software')->label('Include software solutions'),
                        Toggle::make('include_worked_with')->label('Include work connections'),
                    ]),

                Section::make('Draft output')
                    ->schema([
                        Placeholder::make('api_status')
                            ->label('LLM status')
                            ->content(fn () => app(LlmClient::class)->statusSummary()),
                        Textarea::make('output_markdown')
                            ->label('Generated draft (editable)')
                            ->rows(22)
                            ->helperText('Edit freely, then save, download Markdown, or download PDF.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function generate(ApplicationDraftService $service): void
    {
        $state = $this->form->getState();

        try {
            $draft = $service->generate([
                'document_type' => $state['document_type'],
                'job_text' => $state['job_text'] ?? '',
                'position_title' => $state['position_title'] ?? null,
                'institution' => $state['institution'] ?? null,
                'tone' => $state['tone'] ?? 'formal',
                'extra_notes' => $state['extra_notes'] ?? null,
                'publication_ids' => $state['publication_ids'] ?? [],
                'include_scholars' => (bool) ($state['include_scholars'] ?? false),
                'include_activities' => (bool) ($state['include_activities'] ?? false),
                'include_training' => (bool) ($state['include_training'] ?? false),
                'include_consultancy' => (bool) ($state['include_consultancy'] ?? false),
                'include_software' => (bool) ($state['include_software'] ?? false),
                'include_worked_with' => (bool) ($state['include_worked_with'] ?? false),
                'llm_provider' => $state['llm_provider'] ?? LlmClient::PROVIDER_AUTO,
                'user_id' => Auth::id(),
            ]);
        } catch (RuntimeException $e) {
            Notification::make()->title('Generation failed')->body($e->getMessage())->danger()->send();

            return;
        }

        $this->currentDraftId = $draft->id;
        $this->data['output_markdown'] = $draft->output_markdown;

        $body = 'Via '.$draft->providerLabel().($draft->model ? ' / '.$draft->model : '').'. Review carefully before submitting.';
        if ($draft->prompt_tokens || $draft->completion_tokens) {
            $body .= ' Tokens: '.((int) $draft->prompt_tokens + (int) $draft->completion_tokens).'.';
        }
        if (data_get($draft->options, 'failover')) {
            $body .= ' Failover was used.';
        }

        Notification::make()
            ->title('Draft generated')
            ->body($body)
            ->success()
            ->send();
    }

    public function saveDraft(ApplicationDraftService $service): void
    {
        $draft = $this->currentDraft();
        if (! $draft) {
            Notification::make()->title('Nothing to save')->body('Generate a draft first.')->warning()->send();

            return;
        }

        $markdown = (string) ($this->data['output_markdown'] ?? '');
        $service->updateOutput($draft, $markdown);
        Notification::make()->title('Draft saved')->success()->send();
    }

    public function downloadMarkdown(): StreamedResponse|Response
    {
        $draft = $this->currentDraft();
        if (! $draft) {
            Notification::make()->title('Generate a draft first')->warning()->send();

            return response('No draft', 404);
        }

        // Persist latest edits before download
        $markdown = (string) ($this->data['output_markdown'] ?? $draft->output_markdown);
        $draft->update(['output_markdown' => $markdown]);

        $filename = $draft->downloadBasename().'.md';

        return response()->streamDownload(function () use ($markdown) {
            echo $markdown;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    }

    public function downloadPdf(ApplicationPdfService $pdfService): Response
    {
        $draft = $this->currentDraft();
        if (! $draft) {
            Notification::make()->title('Generate a draft first')->warning()->send();

            return response('No draft', 404);
        }

        $markdown = (string) ($this->data['output_markdown'] ?? $draft->output_markdown);
        $draft->update(['output_markdown' => $markdown]);

        return $pdfService->download($draft->fresh());
    }

    public function loadDraft(int $draftId): void
    {
        $draft = ApplicationDraft::query()
            ->when(Auth::id(), fn ($q) => $q->where('user_id', Auth::id()))
            ->find($draftId);

        if (! $draft) {
            Notification::make()->title('Draft not found')->danger()->send();

            return;
        }

        $this->currentDraftId = $draft->id;
        $this->form->fill([
            'document_type' => $draft->document_type,
            'position_title' => $draft->position_title,
            'institution' => $draft->institution,
            'tone' => $draft->tone ?: 'formal',
            'llm_provider' => data_get($draft->options, 'llm_provider_request', config('llm.default', LlmClient::PROVIDER_AUTO)),
            'job_text' => $draft->job_text,
            'extra_notes' => $draft->extra_notes,
            'publication_ids' => $draft->publication_ids ?? [],
            'include_scholars' => (bool) data_get($draft->options, 'include_scholars', true),
            'include_activities' => (bool) data_get($draft->options, 'include_activities', true),
            'include_training' => (bool) data_get($draft->options, 'include_training', false),
            'include_consultancy' => (bool) data_get($draft->options, 'include_consultancy', true),
            'include_software' => (bool) data_get($draft->options, 'include_software', true),
            'include_worked_with' => (bool) data_get($draft->options, 'include_worked_with', true),
            'output_markdown' => $draft->output_markdown,
        ]);

        Notification::make()->title('Draft loaded')->success()->send();
    }

    /**
     * @return \Illuminate\Support\Collection<int, ApplicationDraft>
     */
    public function getRecentDraftsProperty()
    {
        return ApplicationDraft::query()
            ->when(Auth::id(), fn ($q) => $q->where('user_id', Auth::id()))
            ->latest()
            ->limit(12)
            ->get();
    }

    protected function currentDraft(): ?ApplicationDraft
    {
        if (! $this->currentDraftId) {
            return null;
        }

        return ApplicationDraft::query()->find($this->currentDraftId);
    }
}
