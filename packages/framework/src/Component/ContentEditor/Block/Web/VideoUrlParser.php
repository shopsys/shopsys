<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

class VideoUrlParser
{
    public const string PROVIDER_YOUTUBE = 'youtube';
    public const string PROVIDER_VIMEO = 'vimeo';

    protected const array YOUTUBE_WATCH_HOSTS = ['www.youtube.com', 'youtube.com'];
    protected const array YOUTUBE_EMBED_HOSTS = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com'];
    protected const string YOUTUBE_SHORT_HOST = 'youtu.be';
    protected const array VIMEO_HOSTS = ['vimeo.com', 'www.vimeo.com'];
    protected const string VIMEO_PLAYER_HOST = 'player.vimeo.com';

    protected const string YOUTUBE_ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/';
    protected const string VIMEO_ID_PATTERN = '/^\d+$/';

    public function parse(string $url): ?VideoInfo
    {
        $parts = parse_url(trim($url));

        if ($parts === false || !in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $youtubeId = $this->findYoutubeVideoId($host, $path, $query);

        if ($youtubeId !== null) {
            return new VideoInfo(static::PROVIDER_YOUTUBE, $youtubeId, 'https://www.youtube-nocookie.com/embed/' . $youtubeId);
        }

        $vimeoId = $this->findVimeoVideoId($host, $path);

        if ($vimeoId !== null) {
            return new VideoInfo(static::PROVIDER_VIMEO, $vimeoId, 'https://player.vimeo.com/video/' . $vimeoId);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $query
     */
    protected function findYoutubeVideoId(string $host, string $path, array $query): ?string
    {
        $candidate = null;

        if (in_array($host, static::YOUTUBE_WATCH_HOSTS, true) && $path === '/watch') {
            $candidate = $query['v'] ?? null;
        } elseif ($host === static::YOUTUBE_SHORT_HOST) {
            $candidate = substr($path, 1);
        } elseif (in_array($host, static::YOUTUBE_EMBED_HOSTS, true) && str_starts_with($path, '/embed/')) {
            $candidate = substr($path, strlen('/embed/'));
        }

        if (is_string($candidate) && preg_match(static::YOUTUBE_ID_PATTERN, $candidate) === 1) {
            return $candidate;
        }

        return null;
    }

    protected function findVimeoVideoId(string $host, string $path): ?string
    {
        $candidate = null;

        if (in_array($host, static::VIMEO_HOSTS, true)) {
            $candidate = substr($path, 1);
        } elseif ($host === static::VIMEO_PLAYER_HOST && str_starts_with($path, '/video/')) {
            $candidate = substr($path, strlen('/video/'));
        }

        if (is_string($candidate) && preg_match(static::VIMEO_ID_PATTERN, $candidate) === 1) {
            return $candidate;
        }

        return null;
    }
}
