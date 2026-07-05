<?php

namespace App\Support;

class SocialEmbed
{
    public static function youtubeEmbedSrc(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (str_contains($url, 'youtube.com/embed')) {
            return $url;
        }

        if (preg_match('#youtube\.com/@([^/?&]+)#', $url, $matches)) {
            return 'https://www.youtube.com/embed?listType=user_uploads&list='.urlencode($matches[1]);
        }

        if (preg_match('#youtube\.com/channel/([^/?&]+)#', $url, $matches)) {
            return 'https://www.youtube.com/embed/videoseries?list=UU'.substr($matches[1], 2);
        }

        if (preg_match('#youtube\.com/(?:c|user)/([^/?&]+)#', $url, $matches)) {
            return 'https://www.youtube.com/embed?listType=user_uploads&list='.urlencode($matches[1]);
        }

        return null;
    }

    public static function facebookEmbedSrc(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (! str_starts_with($url, 'http')) {
            $url = 'https://'.$url;
        }

        if (str_contains($url, 'facebook.com/plugins/page.php')) {
            return $url;
        }

        $params = http_build_query([
            'href' => $url,
            'tabs' => 'timeline',
            'width' => '500',
            'height' => '500',
            'small_header' => 'false',
            'adapt_container_width' => 'true',
            'hide_cover' => 'false',
            'show_facepile' => 'true',
        ]);

        return 'https://www.facebook.com/plugins/page.php?'.$params;
    }
}
