<?php

namespace App\Support;

use App\Models\Profile;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

class Seo
{
    /**
     * @param  array{
     *   title?: string,
     *   description?: string|null,
     *   canonical?: string|null,
     *   image?: string|null,
     *   type?: string,
     *   robots?: string,
     *   keywords?: string|null,
     * }  $overrides
     * @return array{
     *   title: string,
     *   description: string,
     *   canonical: string,
     *   image: ?string,
     *   type: string,
     *   robots: string,
     *   keywords: ?string,
     *   site_name: string,
     *   locale: string,
     *   twitter_handle: ?string,
     *   json_ld: array<int, array<string, mixed>>,
     * }
     */
    public static function forPage(Profile $profile, array $overrides = []): array
    {
        $siteName = trim((string) (SiteSetting::get('seo_site_name') ?: $profile->name.' Academic Portfolio'));
        $defaultDescription = trim((string) (
            SiteSetting::get('meta_description')
            ?: ($profile->tagline ?: 'Academic portfolio of '.$profile->name)
        ));

        $title = trim((string) ($overrides['title'] ?? ($profile->name.($profile->credentials ? ', '.$profile->credentials : '').' | Academic Portfolio')));
        $description = trim((string) ($overrides['description'] ?? $defaultDescription));
        $description = Str::limit(strip_tags($description), 160, '');

        $canonical = $overrides['canonical'] ?? url()->current();
        $image = $overrides['image'] ?? self::defaultImageUrl($profile);
        $type = $overrides['type'] ?? 'website';
        $robots = $overrides['robots'] ?? (SiteSetting::get('seo_robots', 'index,follow') ?: 'index,follow');
        $keywords = $overrides['keywords'] ?? SiteSetting::get('meta_keywords');
        $twitter = ltrim((string) SiteSetting::get('twitter_handle', ''), '@') ?: null;

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'image' => $image,
            'type' => $type,
            'robots' => $robots,
            'keywords' => filled($keywords) ? (string) $keywords : null,
            'site_name' => $siteName,
            'locale' => str_replace('_', '-', app()->getLocale()),
            'twitter_handle' => $twitter,
            'json_ld' => self::jsonLdGraph($profile, $canonical, $image, $title, $description),
        ];
    }

    public static function defaultImageUrl(Profile $profile): ?string
    {
        $ogPath = SiteSetting::get('og_image_path');
        if (filled($ogPath)) {
            return PublicStorage::url(is_array($ogPath) ? ($ogPath[0] ?? null) : $ogPath);
        }

        return $profile->photoUrl();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function jsonLdGraph(Profile $profile, string $canonical, ?string $image, string $title, string $description): array
    {
        $sameAs = $profile->academicProfiles
            ->pluck('url')
            ->merge($profile->socialLinks->pluck('url'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $person = [
            '@type' => 'Person',
            '@id' => url('/').'#person',
            'name' => $profile->name,
            'url' => url('/'),
            'description' => $description,
        ];

        if (filled($profile->credentials)) {
            $person['honorificSuffix'] = $profile->credentials;
        }
        if (filled($profile->title)) {
            $person['jobTitle'] = $profile->title;
        }
        if (filled($profile->email)) {
            $person['email'] = $profile->email;
        }
        if (filled($image)) {
            $person['image'] = $image;
        }
        if (filled($profile->affiliation)) {
            $person['affiliation'] = [
                '@type' => 'Organization',
                'name' => $profile->affiliation,
            ];
        }
        if (filled($profile->orcid_id)) {
            $person['identifier'] = 'https://orcid.org/'.$profile->orcid_id;
        }
        if ($sameAs !== []) {
            $person['sameAs'] = $sameAs;
        }
        if (filled($profile->research_interests)) {
            $interests = collect(preg_split('/\r\n|\r|\n/', (string) $profile->research_interests) ?: [])
                ->map(fn ($line) => trim($line))
                ->filter()
                ->take(12)
                ->values()
                ->all();
            if ($interests !== []) {
                $person['knowsAbout'] = $interests;
            }
        }

        $website = [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'url' => url('/'),
            'name' => SiteSetting::get('seo_site_name') ?: ($profile->name.' Academic Portfolio'),
            'description' => $description,
            'publisher' => ['@id' => url('/').'#person'],
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
        ];

        $webpage = [
            '@type' => 'WebPage',
            '@id' => $canonical.'#webpage',
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'isPartOf' => ['@id' => url('/').'#website'],
            'about' => ['@id' => url('/').'#person'],
        ];
        if (filled($image)) {
            $webpage['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $image,
            ];
        }

        return [
            [
                '@context' => 'https://schema.org',
                '@graph' => [$person, $website, $webpage],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data  shared view data including profile
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function mergeInto(array $data, array $overrides = []): array
    {
        /** @var Profile $profile */
        $profile = $data['profile'];
        $data['seo'] = self::forPage($profile, $overrides);
        $data['metaDescription'] = $data['seo']['description'];

        return $data;
    }
}
