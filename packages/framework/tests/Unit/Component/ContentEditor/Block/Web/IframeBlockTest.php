<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\IframeBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class IframeBlockTest extends AbstractWebBlockTestCase
{
    /**
     * @return iterable<string, array{url: string, isValid: bool}>
     */
    public static function getUrlConstraintData(): iterable
    {
        yield 'allowed host' => [
            'url' => 'https://player.vimeo.com/video/1',
            'isValid' => true,
        ];

        yield 'other host' => [
            'url' => 'https://example.com/page',
            'isValid' => false,
        ];

        yield 'allowed host as subdomain trick' => [
            'url' => 'https://player.vimeo.com.evil.com/video/1',
            'isValid' => false,
        ];

        yield 'relative url has no host' => [
            'url' => '/page',
            'isValid' => false,
        ];
    }

    #[DataProvider('getUrlConstraintData')]
    public function testUrlConstraint(string $url, bool $isValid): void
    {
        $block = new IframeBlock(['player.vimeo.com']);

        $violations = ContentEditorTestFactory::validateField($block, 'url', $url);

        $this->assertSame($isValid, $violations === []);
    }

    /**
     * @return iterable<string, array{height: int, isValid: bool}>
     */
    public static function getHeightConstraintData(): iterable
    {
        yield 'min' => ['height' => 100, 'isValid' => true];

        yield 'max' => ['height' => 2000, 'isValid' => true];

        yield 'below min' => ['height' => 99, 'isValid' => false];

        yield 'above max' => ['height' => 2001, 'isValid' => false];
    }

    #[DataProvider('getHeightConstraintData')]
    public function testHeightConstraint(int $height, bool $isValid): void
    {
        $violations = ContentEditorTestFactory::validateField(new IframeBlock([]), 'height', $height);

        $this->assertSame($isValid, $violations === []);
    }

    public function testDefaultHeightIsValid(): void
    {
        $block = new IframeBlock([]);
        $defaults = $block->getDefaultData();

        $this->assertSame(400, $defaults['height']);
        $this->assertSame([], ContentEditorTestFactory::validateField($block, 'height', $defaults['height']));
    }
}
