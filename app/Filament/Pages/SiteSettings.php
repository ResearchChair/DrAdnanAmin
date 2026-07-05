<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
        $this->form->fill([
            'accent_color' => SiteSetting::get('accent_color', '#5B2C6F'),
            'secondary_color' => SiteSetting::get('secondary_color', '#C17AA8'),
            'surface_color' => SiteSetting::get('surface_color', '#FFF9F5'),
            'surface_muted_color' => SiteSetting::get('surface_muted_color', '#F5EBE8'),
            'meta_description' => SiteSetting::get('meta_description'),
            'contact_message' => SiteSetting::get('contact_message'),
            'youtube_channel_url' => SiteSetting::get('youtube_channel_url'),
            'youtube_embed_url' => SiteSetting::get('youtube_embed_url'),
            'youtube_daily_rotation' => filter_var(SiteSetting::get('youtube_daily_rotation', '1'), FILTER_VALIDATE_BOOLEAN),
            'youtube_autoplay' => filter_var(SiteSetting::get('youtube_autoplay', '1'), FILTER_VALIDATE_BOOLEAN),
            'youtube_rotation_pool' => SiteSetting::get('youtube_rotation_pool', 30),
            'facebook_page_url' => SiteSetting::get('facebook_page_url'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Branding')->schema([
                    TextInput::make('accent_color')->label('Accent Color')->placeholder('#5B2C6F'),
                    TextInput::make('secondary_color')->label('Secondary Color')->placeholder('#C17AA8'),
                    TextInput::make('surface_color')->label('Background Color')->placeholder('#FFF9F5'),
                    TextInput::make('surface_muted_color')->label('Muted Background')->placeholder('#F5EBE8'),
                ])->columns(2)->description('Onion-inspired palette: deep plum accent, mauve highlight, warm cream backgrounds.'),
                Section::make('SEO & Contact')->schema([
                    Textarea::make('meta_description')->rows(3),
                    Textarea::make('contact_message')->rows(4),
                ]),
                Section::make('Social Embeds')
                    ->description('YouTube picks a different recent upload each day from your channel and can autoplay (muted).')
                    ->schema([
                        TextInput::make('youtube_channel_url')
                            ->label('YouTube channel URL')
                            ->url()
                            ->placeholder('https://www.youtube.com/@YourChannel'),
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

        foreach ($data as $key => $value) {
            SiteSetting::set($key, $value);
        }

        Notification::make()->title('Settings saved')->success()->send();
    }
}
