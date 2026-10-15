<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\MapBlock;
use Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures\ContentEditorTestFactory;

class MapBlockTest extends AbstractWebBlockTestCase
{
    /**
     * @return iterable<string, array{url: string, isValid: bool}>
     */
    public static function getUrlConstraintData(): iterable
    {
        yield 'maps embed' => [
            'url' => 'https://www.google.com/maps/embed?pb=!1m18',
            'isValid' => true,
        ];

        yield 'legacy embed with output parameter' => [
            'url' => 'https://maps.google.com/maps?q=Praha&output=embed',
            'isValid' => true,
        ];

        yield 'legacy embed with parameters after output' => [
            'url' => 'https://maps.google.com/maps?output=embed&q=Praha',
            'isValid' => true,
        ];

        yield 'legacy without output embed' => [
            'url' => 'https://maps.google.com/maps?q=Praha',
            'isValid' => false,
        ];

        yield 'maps page, not embed' => [
            'url' => 'https://www.google.com/maps/place/Praha',
            'isValid' => false,
        ];

        yield 'http' => [
            'url' => 'http://www.google.com/maps/embed?pb=1',
            'isValid' => false,
        ];

        yield 'other host' => [
            'url' => 'https://example.com/maps/embed',
            'isValid' => false,
        ];

        yield 'host as subdomain trick' => [
            'url' => 'https://www.google.com.evil.com/maps/embed',
            'isValid' => false,
        ];
    }

    #[DataProvider('getUrlConstraintData')]
    public function testUrlConstraint(string $url, bool $isValid): void
    {
        $violations = ContentEditorTestFactory::validateField(new MapBlock(), 'url', $url);

        $this->assertSame($isValid, $violations === []);
    }

    public function testUrlIsRequired(): void
    {
        $this->assertTrue(ContentEditorTestFactory::getField(new MapBlock(), 'url')->isRequired());
    }
}
