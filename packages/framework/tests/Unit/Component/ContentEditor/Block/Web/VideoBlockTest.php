<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\VideoBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\VideoUrlParser;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class VideoBlockTest extends AbstractWebBlockTestCase
{
    private VideoBlock $block;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->block = new VideoBlock(new VideoUrlParser());
    }

    /**
     * @return iterable<string, array{url: string, isValid: bool}>
     */
    public static function getUrlConstraintData(): iterable
    {
        yield 'youtube' => [
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'isValid' => true,
        ];

        yield 'vimeo' => [
            'url' => 'https://vimeo.com/123456789',
            'isValid' => true,
        ];

        yield 'other host' => [
            'url' => 'https://example.com/video',
            'isValid' => false,
        ];

        yield 'youtube without id' => [
            'url' => 'https://www.youtube.com/',
            'isValid' => false,
        ];
    }

    #[DataProvider('getUrlConstraintData')]
    public function testUrlConstraint(string $url, bool $isValid): void
    {
        $violations = ContentEditorTestFactory::validateField($this->block, 'url', $url);

        $this->assertSame($isValid, $violations === []);
    }

    public function testExportAddsVideoInfo(): void
    {
        $result = $this->block->export(['url' => 'https://youtu.be/dQw4w9WgXcQ'], 1);

        $this->assertSame([
            'url' => 'https://youtu.be/dQw4w9WgXcQ',
            'provider' => 'youtube',
            'videoId' => 'dQw4w9WgXcQ',
            'embedUrl' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
        ], $result);
    }

    public function testExportOfUnknownUrlKeepsDataAndAddsNulls(): void
    {
        $result = $this->block->export(['url' => 'https://example.com/video'], 1);

        $this->assertSame([
            'url' => 'https://example.com/video',
            'provider' => null,
            'videoId' => null,
            'embedUrl' => null,
        ], $result);
    }

    public function testExtractTextIsEmpty(): void
    {
        $this->assertSame('', $this->block->extractText(['url' => 'https://vimeo.com/123456789']));
    }
}
