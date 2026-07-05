<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
            'youtube_embed_url' => SiteSetting::get('youtube_embed_url'),
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
                    ->description('Optional overrides. By default, embeds use the YouTube academic profile and Facebook social link URLs.')
                    ->schema([
                        TextInput::make('youtube_embed_url')
                            ->label('YouTube embed URL')
                            ->url()
                            ->placeholder('https://www.youtube.com/embed?listType=user_uploads&list=...'),
                        TextInput::make('facebook_page_url')
                            ->label('Facebook page URL override')
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
