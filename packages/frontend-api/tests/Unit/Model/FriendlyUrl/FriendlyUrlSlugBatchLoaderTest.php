<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\FriendlyUrl;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader;

class FriendlyUrlSlugBatchLoaderTest extends TestCase
{
    private const string ROUTE_NAME = 'front_blogcategory_detail';

    private const int CURRENT_DOMAIN_ID = 1;

    public function testSlugsOfCurrentDomainArePrefixedWithSlash(): void
    {
        $friendlyUrlFacade = $this->createMock(FriendlyUrlFacade::class);
        $friendlyUrlFacade->expects($this->once())
            ->method('getMainFriendlyUrlSlugsIndexedByEntityId')
            ->with(self::CURRENT_DOMAIN_ID, self::ROUTE_NAME, [3, 2])
            ->willReturn([3 => 'blog/tips', 2 => 'blog/news']);

        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(self::CURRENT_DOMAIN_ID);

        $friendlyUrlSlugBatchLoader = new FriendlyUrlSlugBatchLoader(new SyncPromiseAdapter(), $domain, $friendlyUrlFacade);

        $this->assertSame(
            [3 => '/blog/tips', 2 => '/blog/news'],
            $friendlyUrlSlugBatchLoader->getSlugsIndexedByEntityId([3, 2], self::ROUTE_NAME),
        );
    }
}
