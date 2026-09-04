<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;

class FriendlyUrlTest extends TestCase
{
    /**
     * @return iterable<string, array{slug: string, expectedSlug: string}>
     */
    public static function slugProvider(): iterable
    {
        yield 'plain slug is kept' => [
            'slug' => 'category/sub-category/',
            'expectedSlug' => 'category/sub-category/',
        ];

        yield 'unicode segment is url encoded' => [
            'slug' => 'kategorie/žluťoučký-kůň/',
            'expectedSlug' => 'kategorie/%C5%BElu%C5%A5ou%C4%8Dk%C3%BD-k%C5%AF%C5%88/',
        ];

        yield 'already encoded segment is not double encoded' => [
            'slug' => 'kategorie/%C5%BElu%C5%A5ou%C4%8Dk%C3%BD-k%C5%AF%C5%88/',
            'expectedSlug' => 'kategorie/%C5%BElu%C5%A5ou%C4%8Dk%C3%BD-k%C5%AF%C5%88/',
        ];
    }

    #[DataProvider('slugProvider')]
    public function testSlugIsNormalizedOnConstruct(string $slug, string $expectedSlug): void
    {
        $friendlyUrl = new FriendlyUrl('front_product_detail', 1, Domain::FIRST_DOMAIN_ID, $slug);

        $this->assertSame($expectedSlug, $friendlyUrl->getSlug());
    }
}
