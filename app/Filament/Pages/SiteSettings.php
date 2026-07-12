<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\ThemePresets;
use App\Support\SocialEmbed;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Site Content';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $accent = SiteSetting::get('accent_color', '#5B2C6F');
        $secondary = SiteSetting::get('secondary_color', '#C17AA8');
        $surface = SiteSetting::get('surface_color', '#FFF9F5');
        $surfaceMuted = SiteSetting::get('surface_muted_color', '#F5EBE8');

        $savedPreset = SiteSetting::get('theme_preset');
        $themePreset = $savedPreset && $savedPreset !== 'custom'
            ? $savedPreset
            : ThemePresets::detectPreset($accent, $secondary, $surface, $surfaceMuted);

        $this->form->fill([
            'theme_preset' => $themePreset,
            'accent_color' => $accent,
            'secondary_color' => $secondary,
            'surface_color' => $surface,
            'surface_muted_color' => $surfaceMuted,
            'meta_description' => SiteSetting::get('meta_description'),
            'meta_keywords' => SiteSetting::get('meta_keywords'),
            'seo_site_name' => SiteSetting::get('seo_site_name'),
            'seo_robots' => SiteSetting::get('seo_robots', 'index,follow'),
            'twitter_handle' => SiteSetting::get('twitter_handle'),
            'og_image_path' => SiteSetting::get('og_image_path'),
            'contact_message' => SiteSetting::get('contact_message'),
            'youtube_channel_url' => SiteSetting::get('youtube_channel_url'),
            'youtube_channel_id' => SiteSetting::get('youtube_channel_id'),
            'youtube_embed_url' => SiteSetting::get('youtube_embed_url'),
            'youtube_daily_rotation' => filter_var(SiteSetting::get('youtube_daily_rotation', '1'), FILTER_VALIDATE_BOOLEAN),
            'youtube_autoplay' => filter_var(SiteSetting::get('youtube_autoplay', '1'), FILTER_VALIDATE_BOOLEAN),
            'youtube_rotation_pool' => SiteSetting::get('youtube_rotation_pool', 30),
            'facebook_page_url' => SiteSetting::get('facebook_page_url'),
            'cv_download_key' => SiteSetting::get('cv_download_key'),
            'cv_require_key' => filter_var(SiteSetting::get('cv_require_key', '1'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Theme & Branding')
                    ->description('Choose a ready-made palette or switch to Custom to fine-tune individual colors.')
                    ->schema([
                        Select::make('theme_preset')
                            ->label('Theme')
                            ->options(fn () => ThemePresets::options() + ['custom' => 'Custom'])
                            ->default('onion')
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->helperText(fn (Get $get): string => ThemePresets::description($get('theme_preset') ?? 'onion')
                                ?? 'Adjust colors manually below.')
                            ->afterStateUpdated(function (?string $state, callable $set): void {
                                if (! $state || $state === 'custom') {
                                    return;
                                }

                                foreach (ThemePresets::applyPreset($state) as $field => $value) {
                                    $set($field, $value);
                                }
                            }),
                        ColorPicker::make('accent_color')
                            ->label('Accent Color')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set) => $set('theme_preset', 'custom')),
                        ColorPicker::make('secondary_color')
                            ->label('Secondary Color')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set) => $set('theme_preset', 'custom')),
                        ColorPicker::make('surface_color')
                            ->label('Background Color')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set) => $set('theme_preset', 'custom')),
                        ColorPicker::make('surface_muted_color')
                            ->label('Muted Background')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set) => $set('theme_preset', 'custom')),
                    ])
                    ->columns(2),
                Section::make('SEO')
                    ->description('Search engines and social sharing. Defaults fall back to your profile photo and site description.')
                    ->schema([
                        TextInput::make('seo_site_name')
                            ->label('Site name')
                            ->placeholder('e.g. Adnan Amin Academic Portfolio')
                            ->helperText('Used for Open Graph site_name and structured data.'),
                        Textarea::make('meta_description')
                            ->label('Default meta description')
                            ->rows(3)
                            ->helperText('Shown in Google results when a page has no custom description. Keep under ~160 characters.'),
                        TextInput::make('meta_keywords')
                            ->label('Keywords (optional)')
                            ->placeholder('artificial intelligence, machine learning, IMSciences')
                            ->helperText('Optional. Modern search engines rely more on content than keywords.'),
                        TextInput::make('twitter_handle')
                            ->label('X / Twitter handle')
                            ->placeholder('username without @'),
                        Select::make('seo_robots')
                            ->label('Robots')
                            ->options([
                                'index,follow' => 'Index & follow (recommended)',
                                'noindex,follow' => 'No index, follow links',
                                'index,nofollow' => 'Index, no follow',
                                'noindex,nofollow' => 'No index, no follow',
                            ])
                            ->native(false)
                            ->default('index,follow'),
                        \Filament\Forms\Components\FileUpload::make('og_image_path')
                            ->label('Social share image (OG)')
                            ->image()
                            ->disk('public')
                            ->directory('seo')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->helperText('Recommended ~1200×630. Falls back to your profile photo if empty.'),
                    ])
                    ->columns(2),
                Section::make('Contact page')->schema([
                    Textarea::make('contact_message')->rows(4),
                ]),
                Section::make('CV Download')
                    ->description('Visitors enter this key on the CV download page. Leave the key empty to allow public downloads without a key.')
                    ->schema([
                        TextInput::make('cv_download_key')
                            ->label('Download key')
                            ->password()
                            ->revealable()
                            ->placeholder('e.g. imsciences2026')
                            ->helperText('Share this key with recruiters, collaborators, or students who need your CV.'),
                        Toggle::make('cv_require_key')
                            ->label('Require key to download CV')
                            ->default(true)
                            ->helperText('When off, anyone can download the CV without entering a key.'),
                    ])->columns(2),
                Section::make('Social Embeds')
                    ->description('YouTube picks a different recent upload each day from your channel and can autoplay (muted). On production, paste your UC… channel ID if the section does not appear.')
                    ->schema([
                        TextInput::make('youtube_channel_url')
                            ->label('YouTube channel URL')
                            ->url()
                            ->placeholder('https://www.youtube.com/@YourChannel'),
                        TextInput::make('youtube_channel_id')
                            ->label('YouTube Channel ID')
                            ->placeholder('UCxxxxxxxxxxxxxxxxxxxxxx')
                            ->helperText('Required on many production hosts. YouTube → your channel → About → Share channel → copy channel ID. Run: php artisan portfolio:test-youtube'),
                        TextInput::make('youtube_embed_url')
                            ->label('Pinned YouTube video (optional)')
                            ->url()
                            ->placeholder('Leave empty for daily rotation from channel'),
                        Toggle::make('youtube_daily_rotation')
                            ->label('Rotate a different channel video each day')
                            ->default(true),
                        Toggle::make('youtube_autoplay')
                            ->label('Autoplay video (muted)')
                            ->default(true),
                        TextInput::make('youtube_rotation_pool')
                            ->label('Videos in rotation pool')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(30)
                            ->default(30)
                            ->helperText('Uses the latest uploads from your channel RSS feed (max 30).'),
                        TextInput::make('facebook_page_url')
                            ->label('Facebook page URL')
                            ->url()
                            ->placeholder('https://www.facebook.com/yourpage'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $channelId = SocialEmbed::normalizeChannelId($data['youtube_channel_id'] ?? null)
            ?? SocialEmbed::extractChannelIdFromUrl($data['youtube_channel_url'] ?? null)
            ?? SocialEmbed::resolveChannelIdFromUrl($data['youtube_channel_url'] ?? null);

        if ($channelId) {
            $data['youtube_channel_id'] = $channelId;
        }

        foreach ($data as $key => $value) {
            if ($key === 'og_image_path' && is_array($value)) {
                $value = $value[0] ?? null;
            }
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            SiteSetting::set($key, $value);
        }

        SocialEmbed::clearYoutubeCache($data['youtube_channel_id'] ?? null);

        $notification = Notification::make()->title('Settings saved');

        if (! empty($data['youtube_channel_url']) && empty($data['youtube_channel_id'])) {
            $notification
                ->body('YouTube channel ID could not be resolved from the URL. Paste the UC… id manually, or run php artisan portfolio:test-youtube on the server.')
                ->warning();
        } else {
            $notification->success();
        }

        $notification->send();
    }
}
