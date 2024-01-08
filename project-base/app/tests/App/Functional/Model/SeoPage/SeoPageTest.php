<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\SeoPage;

use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class SeoPageTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private SeoPageFacade $seoPageFacade;

    /**
     * @inject
     */
    private SeoPageDataFactory $seoPageDataFactory;

    public function testSeoPageMutability(): void
    {
        $domainId = $this->domain->getId();

        $seoPage = $this->seoPageFacade->getByDomainIdAndPageSlug($domainId, SeoPage::SEO_PAGE_HOMEPAGE_SLUG);
        $seoPageData = $this->seoPageDataFactory->createFromSeoPage($seoPage);

        $seoPageSlug = $seoPageData->pageSlugsIndexedByDomainId[$domainId];
        $seoPageTitle = $seoPageData->seoTitlesIndexedByDomainId[$domainId];

        $proposedPageSlug = 'new-homepage-slug';
        $proposedSeoPageTitle = 'new homepage title';

        $seoPageData->pageSlugsIndexedByDomainId[$domainId] = $proposedPageSlug;
        $seoPageData->seoTitlesIndexedByDomainId[$domainId] = $proposedSeoPageTitle;

        $this->seoPageFacade->edit($seoPage->getId(), $seoPageData);
        $updatedSeoPageId = $seoPage->getId();

        $this->em->clear();

        $updatedSeoPage = $this->seoPageFacade->getById($updatedSeoPageId);
        $updatedSeoPageData = $this->seoPageDataFactory->createFromSeoPage($updatedSeoPage);

        $updatedSeoPageSlug = $updatedSeoPageData->pageSlugsIndexedByDomainId[$domainId];
        $updatedSeoPageTitle = $updatedSeoPageData->seoTitlesIndexedByDomainId[$domainId];

        $this->assertNotEquals($seoPageSlug, $proposedPageSlug);
        $this->assertNotEquals($seoPageTitle, $proposedSeoPageTitle);

        $this->assertNotEquals($updatedSeoPageSlug, $proposedPageSlug);
        $this->assertSame($updatedSeoPageTitle, $proposedSeoPageTitle);
    }
}
