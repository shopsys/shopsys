<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlGenerator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Contracts\Cache\CacheInterface;

class FriendlyUrlGeneratorTest extends TestCase
{
    #[DataProvider('friendlyUrlSlugProvider')]
    public function testGetGeneratedUrlDoesNotDoubleEncodeFriendlyUrlSlug(string $slug, string $expectedUrl): void
    {
        $friendlyUrlGenerator = $this->createFriendlyUrlGenerator();
        $friendlyUrl = new FriendlyUrl('route_name', 1, 1, $slug);
        $url = $friendlyUrlGenerator->getGeneratedUrl(
            'route_name',
            new Route('friendly-url'),
            $friendlyUrl,
            [],
            UrlGeneratorInterface::ABSOLUTE_PATH,
        );

        $this->assertSame($expectedUrl, $url);
    }

    /**
     * @return array<int, array{string, string}>
     */
    public static function friendlyUrlSlugProvider(): array
    {
        return [
            ['10%', '/10%25'],
            ['10%25', '/10%25'],
            ['caf%C3%A9/', '/caf%C3%A9/'],
            ['café/', '/caf%C3%A9/'],
        ];
    }

    private function createFriendlyUrlGenerator(): FriendlyUrlGenerator
    {
        return new FriendlyUrlGenerator(
            new RequestContext(),
            $this->createStub(FriendlyUrlRepository::class),
            $this->createStub(FriendlyUrlCacheKeyProvider::class),
            $this->createStub(CacheInterface::class),
        );
    }
}
