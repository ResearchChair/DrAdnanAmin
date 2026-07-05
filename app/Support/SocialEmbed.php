<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SocialEmbed
{
    public static function youtubeEmbedSrc(?string $url, bool $rotateDaily = true): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (preg_match('#(?:youtube\.com/watch\?.*v=|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $matches)) {
            return self::embedVideo($matches[1], rotateDaily: false);
        }

        if (str_contains($url, 'youtube.com/embed')) {
            return self::normalizeYoutubeEmbedUrl($url);
        }

        if (preg_match('#youtube\.com/@([^/?&]+)#', $url, $matches)) {
            return self::embedFromHandle($matches[1], $rotateDaily);
        }

        if (preg_match('#youtube\.com/channel/([^/?&]+)#', $url, $matches)) {
            return self::embedFromChannelId($matches[1], $rotateDaily);
        }

        if (preg_match('#youtube\.com/(?:c|user)/([^/?&]+)#', $url, $matches)) {
            return self::embedFromHandle($matches[1], $rotateDaily);
        }

        return null;
    }

    public static function youtubePageUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (str_contains($url, 'youtube.com/embed')) {
            return null;
        }

        if (! str_starts_with($url, 'http')) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    public static function youtubeAutoplayEnabled(): bool
    {
        return self::settingBool('youtube_autoplay', (bool) config('academic.youtube_autoplay', true));
    }

    public static function youtubeDailyRotationEnabled(): bool
    {
        return self::settingBool('youtube_daily_rotation', (bool) config('academic.youtube_daily_rotation', true));
    }

    public static function facebookEmbedSrc(?string $url): ?string
    {
        $pageUrl = self::facebookPageUrl($url);

        if (! $pageUrl) {
            return null;
        }

        $params = http_build_query([
            'href' => $pageUrl,
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

    public static function facebookPageUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);

        if (str_contains($url, 'facebook.com/plugins/page.php')) {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);

            return $query['href'] ?? null;
        }

        if (! str_starts_with($url, 'http')) {
            $url = 'https://'.$url;
        }

        $url = preg_replace('#^https?://facebook\.com#', 'https://www.facebook.com', $url);
        $url = preg_replace('#^http://#', 'https://', $url);

        return $url;
    }

    protected static function normalizeYoutubeEmbedUrl(string $url): string
    {
        if (preg_match('#/embed/([A-Za-z0-9_-]{11})#', $url, $matches)) {
            return self::embedVideo($matches[1], rotateDaily: false);
        }

        if (preg_match('/[?&]listType=user_uploads/', $url) && preg_match('/[?&]list=([^&]+)/', $url, $matches)) {
            $handle = ltrim(urldecode($matches[1]), '@');
            $channelId = self::resolveChannelIdFromHandle($handle);

            if ($channelId) {
                return self::embedFromChannelId($channelId);
            }
        }

        if (preg_match('/[?&]list=([^&]+)/', $url, $matches)) {
            $list = urldecode($matches[1]);

            if (str_starts_with($list, 'UU') || str_starts_with($list, 'PL')) {
                return 'https://www.youtube.com/embed/playlist?list='.urlencode($list);
            }
        }

        return preg_replace('/([?&]list=)@([^&]+)/', '$1$2', $url);
    }

    protected static function embedFromHandle(string $handle, bool $rotateDaily = true): ?string
    {
        $handle = ltrim(trim($handle), '@');
        $channelId = self::resolveChannelIdFromHandle($handle);

        if ($channelId) {
            return self::embedFromChannelId($channelId, $rotateDaily);
        }

        return null;
    }

    protected static function embedFromChannelId(string $channelId, bool $rotateDaily = true): ?string
    {
        $channelId = str_replace('https://www.youtube.com/channel/', '', trim($channelId));

        if (! str_starts_with($channelId, 'UC')) {
            return null;
        }

        $useRotation = $rotateDaily && self::youtubeDailyRotationEnabled();
        $videoId = $useRotation
            ? self::resolveDailyVideoIdFromChannel($channelId)
            : self::fetchRecentVideoIdsFromChannel($channelId)[0] ?? null;

        if ($videoId) {
            return self::embedVideo($videoId, rotateDaily: $useRotation);
        }

        $uploadsPlaylist = str_replace('UC', 'UU', $channelId);

        return 'https://www.youtube.com/embed/playlist?list='.$uploadsPlaylist;
    }

    protected static function embedVideo(string $videoId, bool $rotateDaily = true): string
    {
        $params = [
            'rel' => '0',
            'modestbranding' => '1',
        ];

        if ($rotateDaily && self::youtubeAutoplayEnabled()) {
            $params['autoplay'] = '1';
            $params['mute'] = '1';
        }

        return 'https://www.youtube.com/embed/'.$videoId.'?'.http_build_query($params);
    }

    protected static function resolveDailyVideoIdFromChannel(string $channelId): ?string
    {
        $videos = self::fetchRecentVideoIdsFromChannel($channelId);

        if ($videos === []) {
            return null;
        }

        $index = crc32(now()->toDateString().$channelId) % count($videos);

        return $videos[$index];
    }

    /**
     * @return list<string>
     */
    protected static function fetchRecentVideoIdsFromChannel(string $channelId): array
    {
        $limit = max(1, (int) SiteSetting::get(
            'youtube_rotation_pool',
            config('academic.youtube_rotation_pool', 30)
        ));

        return Cache::remember("youtube_recent_videos:{$channelId}:{$limit}", now()->addHours(12), function () use ($channelId, $limit) {
            try {
                $response = AcademicHttp::client()->get(
                    "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}"
                );

                if (! $response->successful()) {
                    return [];
                }

                preg_match_all('/<yt:videoId>([^<]+)<\/yt:videoId>/', $response->body(), $matches);

                return array_values(array_slice($matches[1] ?? [], 0, $limit));
            } catch (\Throwable) {
                return [];
            }
        });
    }

    protected static function resolveChannelIdFromHandle(string $handle): ?string
    {
        $handle = ltrim(trim($handle), '@');

        return Cache::remember("youtube_channel_id:{$handle}", now()->addDay(), function () use ($handle) {
            try {
                $response = AcademicHttp::client()
                    ->withHeaders(['Accept-Language' => 'en-US,en;q=0.9'])
                    ->get("https://www.youtube.com/@{$handle}");

                if (! $response->successful()) {
                    return null;
                }

                $body = $response->body();

                if (preg_match('/"channelId":"(UC[^"]+)"/', $body, $matches)) {
                    return $matches[1];
                }

                if (preg_match('/"browseId":"(UC[^"]+)"/', $body, $matches)) {
                    return $matches[1];
                }

                if (preg_match('/meta itemprop="channelId" content="(UC[^"]+)"/', $body, $matches)) {
                    return $matches[1];
                }
            } catch (\Throwable) {
                return null;
            }

            return null;
        });
    }

    protected static function settingBool(string $key, bool $default): bool
    {
        $value = SiteSetting::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
