<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlCacheKeyProvider;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlUniqueResultFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class FriendlyUrlFacadeTest extends TestCase
{
    private const string ROUTE_NAME = 'front_blogcategory_detail';

    private const int FIRST_DOMAIN_ID = 1;

    private const int SECOND_DOMAIN_ID = 2;

    public function testMainFriendlyUrlSlugsAreLoadedFromRepositoryAndCached(): void
    {
        $friendlyUrlRepository = $this->createMock(FriendlyUrlRepository::class);
        $friendlyUrlRepository->expects($this->once())
            ->method('getMainFriendlyUrlsByEntitiesIndexedByEntityId')
            ->with([3, 2], self::ROUTE_NAME, self::SECOND_DOMAIN_ID)
            ->willReturn([
                3 => $this->createFriendlyUrlStub('blog/tipy'),
                2 => $this->createFriendlyUrlStub('blog/novinky'),
            ]);

        $friendlyUrlFacade = $this->createFriendlyUrlFacade($friendlyUrlRepository);

        $slugs = $friendlyUrlFacade->getMainFriendlyUrlSlugsIndexedByEntityId(self::SECOND_DOMAIN_ID, self::ROUTE_NAME, [3, 2]);

        $this->assertSame([3 => 'blog/tipy', 2 => 'blog/novinky'], $slugs);
        $this->assertSame($slugs, $friendlyUrlFacade->getMainFriendlyUrlSlugsIndexedByEntityId(self::SECOND_DOMAIN_ID, self::ROUTE_NAME, [3, 2]));
    }

    public function testCachedMainFriendlyUrlSlugOfOneDomainDoesNotShadowAnotherDomain(): void
    {
        $friendlyUrlRepository = $this->createMock(FriendlyUrlRepository::class);
        $friendlyUrlRepository->expects($this->exactly(2))
            ->method('getMainFriendlyUrlsByEntitiesIndexedByEntityId')
            ->willReturnMap([
                [[3], self::ROUTE_NAME, self::SECOND_DOMAIN_ID, [3 => $this->createFriendlyUrlStub('blog/tipy')]],
                [[3], self::ROUTE_NAME, self::FIRST_DOMAIN_ID, [3 => $this->createFriendlyUrlStub('blog/tips')]],
            ]);

        $friendlyUrlFacade = $this->createFriendlyUrlFacade($friendlyUrlRepository);

        $this->assertSame([3 => 'blog/tipy'], $friendlyUrlFacade->getMainFriendlyUrlSlugsIndexedByEntityId(self::SECOND_DOMAIN_ID, self::ROUTE_NAME, [3]));
        $this->assertSame([3 => 'blog/tips'], $friendlyUrlFacade->getMainFriendlyUrlSlugsIndexedByEntityId(self::FIRST_DOMAIN_ID, self::ROUTE_NAME, [3]));
    }

    public function testMissingMainFriendlyUrlThrowsException(): void
    {
        $friendlyUrlRepository = $this->createStub(FriendlyUrlRepository::class);
        $friendlyUrlRepository->method('getMainFriendlyUrlsByEntitiesIndexedByEntityId')->willReturn([]);

        $friendlyUrlFacade = $this->createFriendlyUrlFacade($friendlyUrlRepository);

        $this->expectException(FriendlyUrlNotFoundException::class);
        $this->expectExceptionMessage('Main friendly URL not found for route "front_blogcategory_detail", domain ID "2", and entity ID "3".');

        $friendlyUrlFacade->getMainFriendlyUrlSlugsIndexedByEntityId(self::SECOND_DOMAIN_ID, self::ROUTE_NAME, [3]);
    }

    private function createFriendlyUrlFacade(FriendlyUrlRepository $friendlyUrlRepository): FriendlyUrlFacade
    {
        return new FriendlyUrlFacade(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(DomainRouterFactory::class),
            $this->createStub(FriendlyUrlUniqueResultFactory::class),
            $friendlyUrlRepository,
            $this->createStub(Domain::class),
            $this->createStub(FriendlyUrlFactory::class),
            new FriendlyUrlCacheKeyProvider(),
            new ArrayAdapter(),
            $this->createStub(ClockInterface::class),
        );
    }

    private function createFriendlyUrlStub(string $slug): FriendlyUrl
    {
        $friendlyUrl = $this->createStub(FriendlyUrl::class);
        $friendlyUrl->method('getSlug')->willReturn($slug);

        return $friendlyUrl;
    }
}
