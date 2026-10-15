<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\VideoUrlParser;

class VideoUrlParserTest extends TestCase
{
    /**
     * @return iterable<string, array{url: string, provider: string, videoId: string, embedUrl: string}>
     */
    public static function getValidUrlData(): iterable
    {
        yield 'youtube watch' => [
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'provider' => 'youtube',
            'videoId' => 'dQw4w9WgXcQ',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ];

        yield 'youtube watch with more parameters' => [
            'url' => 'https://www.youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=10',
            'provider' => 'youtube',
            'videoId' => 'dQw4w9WgXcQ',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ];

        yield 'youtu.be' => [
            'url' => 'https://youtu.be/dQw4w9WgXcQ',
            'provider' => 'youtube',
            'videoId' => 'dQw4w9WgXcQ',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ];

        yield 'youtube embed' => [
            'url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'provider' => 'youtube',
            'videoId' => 'dQw4w9WgXcQ',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ];

        yield 'youtube-nocookie embed' => [
            'url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'provider' => 'youtube',
            'videoId' => 'dQw4w9WgXcQ',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ];

        yield 'youtube id with dash and underscore' => [
            'url' => 'https://youtu.be/a-b_c-d_e-f',
            'provider' => 'youtube',
            'videoId' => 'a-b_c-d_e-f',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/a-b_c-d_e-f',
        ];

        yield 'vimeo' => [
            'url' => 'https://vimeo.com/123456789',
            'provider' => 'vimeo',
            'videoId' => '123456789',
            'embedUrl' => 'https://player.vimeo.com/video/123456789',
        ];

        yield 'vimeo player' => [
            'url' => 'https://player.vimeo.com/video/123456789',
            'provider' => 'vimeo',
            'videoId' => '123456789',
            'embedUrl' => 'https://player.vimeo.com/video/123456789',
        ];
    }

    #[DataProvider('getValidUrlData')]
    public function testParseValidUrl(string $url, string $provider, string $videoId, string $embedUrl): void
    {
        $result = (new VideoUrlParser())->parse($url);

        $this->assertNotNull($result);
        $this->assertSame($provider, $result->provider);
        $this->assertSame($videoId, $result->videoId);
        $this->assertSame($embedUrl, $result->embedUrl);
    }

    /**
     * @return iterable<string, array{url: string}>
     */
    public static function getInvalidUrlData(): iterable
    {
        yield 'other host' => [
            'url' => 'https://example.com/watch?v=dQw4w9WgXcQ',
        ];

        yield 'host only suffix matches' => [
            'url' => 'https://evil-youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        yield 'youtube without video' => [
            'url' => 'https://www.youtube.com/',
        ];

        yield 'youtube id too short' => [
            'url' => 'https://youtu.be/abc',
        ];

        yield 'youtube id with forbidden character' => [
            'url' => 'https://youtu.be/dQw4w9WgXc!',
        ];

        yield 'vimeo id not numeric' => [
            'url' => 'https://vimeo.com/channels',
        ];

        yield 'not http scheme' => [
            'url' => 'javascript://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ];

        yield 'relative url' => [
            'url' => '/watch?v=dQw4w9WgXcQ',
        ];

        yield 'empty' => [
            'url' => '',
        ];
    }

    #[DataProvider('getInvalidUrlData')]
    public function testParseInvalidUrl(string $url): void
    {
        $this->assertNull((new VideoUrlParser())->parse($url));
    }
}
