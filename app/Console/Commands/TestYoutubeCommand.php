<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use App\Support\SocialEmbed;
use Illuminate\Console\Command;

class TestYoutubeCommand extends Command
{
    protected $signature = 'portfolio:test-youtube {--clear-cache : Clear cached YouTube lookups before testing}';

    protected $description = 'Diagnose YouTube embed configuration (channel ID, RSS feed, embed URL)';

    public function handle(): int
    {
        $channelUrl = SiteSetting::get('youtube_channel_url');
        $channelId = SiteSetting::get('youtube_channel_id') ?: config('academic.youtube_channel_id');
        $pinnedUrl = SiteSetting::get('youtube_embed_url');

        $this->info('YouTube configuration');
        $this->line('  Channel URL: '.($channelUrl ?: '(not set)'));
        $this->line('  Channel ID:  '.($channelId ?: '(not set)'));
        $this->line('  Pinned URL:  '.($pinnedUrl ?: '(not set)'));

        if ($this->option('clear-cache')) {
            SocialEmbed::clearYoutubeCache($channelId);
            $this->warn('Cleared YouTube cache.');
        }

        if ($channelUrl && ! $channelId) {
            $this->newLine();
            $this->comment('Resolving channel ID from URL…');
            $resolved = SocialEmbed::resolveChannelIdFromUrl($channelUrl);
            $this->line('  Resolved: '.($resolved ?: 'FAILED'));

            if ($resolved) {
                $channelId = $resolved;
                $this->info('Save this in Admin → Site Settings → YouTube Channel ID: '.$resolved);
            }
        }

        if ($channelId) {
            $rss = "https://www.youtube.com/feeds/videos.xml?channel_id={$channelId}";
            $this->newLine();
            $this->comment('Testing RSS feed…');
            $this->line('  '.$rss);
        }

        $embed = SocialEmbed::resolveHomeYoutubeEmbed($channelUrl, $pinnedUrl, $channelId);

        $this->newLine();
        if ($embed) {
            $this->info('Embed URL:');
            $this->line('  '.$embed);

            return self::SUCCESS;
        }

        $this->error('Could not build a YouTube embed URL.');
        $this->newLine();
        $this->line('Fix on production:');
        $this->line('  1. Open your channel on YouTube → About → Share channel → copy channel ID (starts with UC)');
        $this->line('  2. Admin → Site Settings → paste into "YouTube Channel ID"');
        $this->line('  3. Or set YOUTUBE_CHANNEL_ID=UCxxxxxxxx in production .env');
        $this->line('  4. Run: php artisan portfolio:test-youtube --clear-cache');
        $this->line('  5. Check storage/logs/laravel.log for SSL or outbound HTTP errors');

        return self::FAILURE;
    }
}
