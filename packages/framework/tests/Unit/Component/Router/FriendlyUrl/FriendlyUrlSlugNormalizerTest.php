<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlSlugNormalizer;

class FriendlyUrlSlugNormalizerTest extends TestCase
{
    public static function normalizationDataProvider(): array
    {
        return [
            ['simple-slug/', 'simple-slug/'],
            ['caf%C3%A9/', 'caf%C3%A9/'],
            ['caf%c3%a9/', 'caf%C3%A9/'],
            ['café/', 'caf%C3%A9/'],
            ['folder/caf%C3%A9/', 'folder/caf%C3%A9/'],
            ['10%', '10%25'],
            ['new-%75rl/', 'new-url/'],
            ['a%2Fb', 'a/b'],
        ];
    }

    #[DataProvider('normalizationDataProvider')]
    public function testNormalize(string $slug, string $expectedNormalizedSlug): void
    {
        $normalizedSlug = FriendlyUrlSlugNormalizer::normalize($slug);

        self::assertSame($expectedNormalizedSlug, $normalizedSlug);
    }
}
