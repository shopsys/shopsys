<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Form\FriendlyUrlType;

class FriendlyUrlTypeTest extends TestCase
{
    public static function validSlugsProvider(): array
    {
        return [
            ['new-url/'],
            ['folder/new-url_123/'],
            ['caf%C3%A9'],
            ['folder/%C5%BElu%C5%A5ou%C4%8Dk%C3%BD-k%C5%AF%C5%88/'],
        ];
    }

    public static function invalidSlugsProvider(): array
    {
        return [
            ['café'],
            ['folder/%'],
            ['folder/%2'],
            ['folder/%ZZ'],
            ['folder/with space'],
        ];
    }

    #[DataProvider('validSlugsProvider')]
    public function testSlugRegexMatchesValidEncodedSlug(string $slug): void
    {
        self::assertMatchesRegularExpression(FriendlyUrlType::SLUG_REGEX, $slug);
    }

    #[DataProvider('invalidSlugsProvider')]
    public function testSlugRegexDoesNotMatchInvalidOrNonEncodedSlug(string $slug): void
    {
        self::assertDoesNotMatchRegularExpression(FriendlyUrlType::SLUG_REGEX, $slug);
    }
}
