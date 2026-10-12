<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\FriendlyUrl;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class FriendlyUrlSlugBatchLoaderTest extends TestCase
{
    private const string ROUTE_NAME = 'front_blogcategory_detail';

    private const int CURRENT_DOMAIN_ID = 1;

    private const int OTHER_DOMAIN_ID = 2;

    public function testMainFriendlyUrlSlugsOnOtherDomainAreLoadedFromRepositoryAndCached(): void
    {
        $cache = new ArrayAdapter();
        $friendlyUrlRepository = $this->createMock(FriendlyUrlRepository::class);
        $friendlyUrlRepository->expects($this->once())
            ->method('getMainFriendlyUrlsByEntitiesIndexedByEntityId')
            ->with([3, 2], self::ROUTE_NAME, self::OTHER_DOMAIN_ID)
            ->willReturn([
                3 => $this->createFriendlyUrlStub('blog/tipy'),
                2 => $this->createFriendlyUrlStub('blog/novinky'),
            ]);

        $loader = $this->createLoader($friendlyUrlRepository, $cache);

        $mainFriendlyUrlSlugs = $loader->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain([3, 2], self::ROUTE_NAME, self::OTHER_DOMAIN_ID);

        $this->assertSame([3 => 'blog/tipy', 2 => 'blog/novinky'], $mainFriendlyUrlSlugs);
        $this->assertSame($mainFriendlyUrlSlugs, $loader->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain([3, 2], self::ROUTE_NAME, self::OTHER_DOMAIN_ID));
    }

    public function testCachedSlugOfOtherDomainDoesNotShadowCurrentDomain(): void
    {
        $cache = new ArrayAdapter();
        $friendlyUrlRepository = $this->createMock(FriendlyUrlRepository::class);
        $friendlyUrlRepository->expects($this->exactly(2))
            ->method('getMainFriendlyUrlsByEntitiesIndexedByEntityId')
            ->willReturnMap([
                [[3], self::ROUTE_NAME, self::OTHER_DOMAIN_ID, [3 => $this->createFriendlyUrlStub('blog/tipy')]],
                [[3], self::ROUTE_NAME, self::CURRENT_DOMAIN_ID, [3 => $this->createFriendlyUrlStub('blog/tips')]],
            ]);

        $loader = $this->createLoader($friendlyUrlRepository, $cache);

        $this->assertSame([3 => 'blog/tipy'], $loader->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain([3], self::ROUTE_NAME, self::OTHER_DOMAIN_ID));
        $this->assertSame([3 => '/blog/tips'], $loader->getSlugsIndexedByEntityId([3], self::ROUTE_NAME));
    }

    public function testMissingMainFriendlyUrlThrowsException(): void
    {
        $friendlyUrlRepository = $this->createStub(FriendlyUrlRepository::class);
        $friendlyUrlRepository->method('getMainFriendlyUrlsByEntitiesIndexedByEntityId')->willReturn([]);

        $loader = $this->createLoader($friendlyUrlRepository, new ArrayAdapter());

        $this->expectException(FriendlyUrlNotFoundException::class);
        $this->expectExceptionMessage('Main friendly URL not found for route "front_blogcategory_detail", domain ID "2", and entity ID "3".');

        $loader->getMainFriendlyUrlSlugsIndexedByEntityIdOnDomain([3], self::ROUTE_NAME, self::OTHER_DOMAIN_ID);
    }

    private function createLoader(
        FriendlyUrlRepository $friendlyUrlRepository,
        ArrayAdapter $cache,
    ): FriendlyUrlSlugBatchLoader {
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(self::CURRENT_DOMAIN_ID);

        $friendlyUrlCacheKeyProvider = $this->createStub(FriendlyUrlCacheKeyProvider::class);
        $friendlyUrlCacheKeyProvider->method('getMainFriendlyUrlSlugCacheKey')
            ->willReturnCallback(static fn (string $routeName, int $domainId, int $entityId): string => sprintf('%s_%d_%d', $routeName, $domainId, $entityId));

        return new FriendlyUrlSlugBatchLoader(
            new SyncPromiseAdapter(),
            $domain,
            $friendlyUrlRepository,
            $cache,
            $friendlyUrlCacheKeyProvider,
        );
    }

    private function createFriendlyUrlStub(string $slug): FriendlyUrl
    {
        $friendlyUrl = $this->createStub(FriendlyUrl::class);
        $friendlyUrl->method('getSlug')->willReturn($slug);

        return $friendlyUrl;
    }
}
