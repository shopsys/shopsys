<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\SeoPage;

use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDomain;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Shopsys\FrameworkBundle\Model\Seo\SeoMetaRobotsEnum;
use Tests\App\Test\TransactionFunctionalTestCase;

class SeoPageTest extends TransactionFunctionalTestCase
{
    private const int HOMEPAGE_SEO_PAGE_ID = 1;

    /**
     * @inject
     */
    private SeoPageFacade $seoPageFacade;

    /**
     * @inject
     */
    private SeoPageDataFactory $seoPageDataFactory;

    /**
     * Slugs of the storefront pages seeded by the migration Version20260909110000
     */
    private const array SEEDED_PAGE_SLUGS_BY_LOCALE = [
        'en' => [
            'cart' => 'cart',
            'search' => 'search',
            'wishlist' => 'wishlist',
            'brands' => 'brands-overview',
        ],
        'cs' => [
            'cart' => 'kosik',
            'search' => 'hledani',
            'wishlist' => 'oblibene-produkty',
            'brands' => 'prehled-znacek',
        ],
        'sk' => [
            'cart' => 'kosik',
            'search' => 'hladanie',
            'wishlist' => 'oblubene-produkty',
            'brands' => 'prehlad-znaciek',
        ],
    ];

    public function testSeededStorefrontPagesHaveDefaultRobots(): void
    {
        $domainId = $this->domain->getId();
        $slugs = self::SEEDED_PAGE_SLUGS_BY_LOCALE[$this->domain->getLocale()] ?? self::SEEDED_PAGE_SLUGS_BY_LOCALE['en'];

        $cartPage = $this->seoPageFacade->getByDomainIdAndPageSlug($domainId, $slugs['cart']);
        $searchPage = $this->seoPageFacade->getByDomainIdAndPageSlug($domainId, $slugs['search']);
        $wishlistPage = $this->seoPageFacade->getByDomainIdAndPageSlug($domainId, $slugs['wishlist']);
        $brandsPage = $this->seoPageFacade->getByDomainIdAndPageSlug($domainId, $slugs['brands']);

        $this->assertSame(SeoMetaRobotsEnum::NOINDEX, $cartPage->getSeoAttributes($domainId)->getMetaRobots());
        $this->assertSame(SeoMetaRobotsEnum::NOINDEX_NOFOLLOW, $searchPage->getSeoAttributes($domainId)->getMetaRobots());
        $this->assertSame(SeoMetaRobotsEnum::NOINDEX, $wishlistPage->getSeoAttributes($domainId)->getMetaRobots());
        $this->assertNull($brandsPage->getSeoAttributes($domainId)->getMetaRobots());
    }

    public function testSeoPageMutability(): void
    {
        $domainId = $this->domain->getId();

        $seoPage = $this->seoPageFacade->getById(self::HOMEPAGE_SEO_PAGE_ID);
        $seoPageData = $this->seoPageDataFactory->createFromSeoPage($seoPage);

        $seoPageName = $seoPageData->pageName;
        $seoPageSlug = $seoPageData->pageSlugsIndexedByDomainId[$domainId];
        $seoPageTitle = $seoPageData->seo[$domainId]->title;

        $proposedPageName = 'New homepage name';
        $proposedPageSlug = 'new-homepage-slug';
        $proposedSeoPageTitle = 'new homepage title';

        $seoPageData->pageName = $proposedPageName;
        $seoPageData->pageSlugsIndexedByDomainId[$domainId] = $proposedPageSlug;
        $seoPageData->seo[$domainId]->title = $proposedSeoPageTitle;

        $this->seoPageFacade->edit($seoPage->getId(), $seoPageData);
        $updatedSeoPageId = $seoPage->getId();

        $this->em->clear();

        $updatedSeoPage = $this->seoPageFacade->getById($updatedSeoPageId);
        $updatedSeoPageData = $this->seoPageDataFactory->createFromSeoPage($updatedSeoPage);

        $updatedSeoPageName = $updatedSeoPageData->pageName;
        $updatedSeoPageSlug = $updatedSeoPageData->pageSlugsIndexedByDomainId[$domainId];
        $updatedSeoPageTitle = $updatedSeoPageData->seo[$domainId]->title;

        $this->assertNotEquals($seoPageName, $proposedPageName);
        $this->assertNotEquals($seoPageSlug, $proposedPageSlug);
        $this->assertNotEquals($seoPageTitle, $proposedSeoPageTitle);

        $this->assertSame($proposedPageName, $updatedSeoPageName);
        $this->assertSame($proposedPageSlug, $updatedSeoPageSlug);
        $this->assertSame($proposedSeoPageTitle, $updatedSeoPageTitle);
    }

    public function testSeoPageWithMissingDomainCanBeEditedAndTheDomainIsRestoredOnSave(): void
    {
        $domainId = $this->domain->getId();

        $this->em->createQueryBuilder()
            ->delete(SeoPageDomain::class, 'spd')
            ->where('spd.seoPage = :seoPageId')
            ->andWhere('spd.domainId = :domainId')
            ->setParameter('seoPageId', self::HOMEPAGE_SEO_PAGE_ID)
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->execute();
        $this->em->clear();

        $seoPage = $this->seoPageFacade->getById(self::HOMEPAGE_SEO_PAGE_ID);
        $seoPageData = $this->seoPageDataFactory->createFromSeoPage($seoPage);

        $this->assertNull($seoPageData->pageSlugsIndexedByDomainId[$domainId]);
        $this->assertNull($seoPageData->seo[$domainId]->title);

        $this->seoPageFacade->edit($seoPage->getId(), $seoPageData);
        $this->em->clear();

        $this->assertSame(0, $this->countSeoPageDomains(self::HOMEPAGE_SEO_PAGE_ID, $domainId));

        $seoPage = $this->seoPageFacade->getById(self::HOMEPAGE_SEO_PAGE_ID);
        $seoPageData = $this->seoPageDataFactory->createFromSeoPage($seoPage);
        $seoPageData->pageSlugsIndexedByDomainId[$domainId] = 'restored-homepage-slug';
        $seoPageData->seo[$domainId]->title = 'restored homepage title';

        $this->seoPageFacade->edit($seoPage->getId(), $seoPageData);
        $this->em->clear();

        $restoredSeoPage = $this->seoPageFacade->getById(self::HOMEPAGE_SEO_PAGE_ID);

        $this->assertSame('restored-homepage-slug', $restoredSeoPage->getPageSlug($domainId));
        $this->assertSame('restored homepage title', $restoredSeoPage->getSeoAttributes($domainId)->getTitle());
    }

    private function countSeoPageDomains(int $seoPageId, int $domainId): int
    {
        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(spd.id)')
            ->from(SeoPageDomain::class, 'spd')
            ->where('spd.seoPage = :seoPageId')
            ->andWhere('spd.domainId = :domainId')
            ->setParameter('seoPageId', $seoPageId)
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
