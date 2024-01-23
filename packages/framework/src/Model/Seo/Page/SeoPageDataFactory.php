<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;

class SeoPageDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData
     */
    public function create(): SeoPageData
    {
        $seoPageData = new SeoPageData();
        $seoPageData->seoOgImage = $this->imageUploadDataFactory->create();

        return $seoPageData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData
     */
    public function createFromSeoPage(SeoPage $seoPage): SeoPageData
    {
        $seoPageData = $this->create();
        $this->fillFromSeoPage($seoPageData, $seoPage);

        return $seoPageData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageData $seoPageData
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
     */
    protected function fillFromSeoPage(SeoPageData $seoPageData, SeoPage $seoPage): void
    {
        $seoPageData->pageName = $seoPage->getPageName();

        foreach ($this->domain->getAll() as $domain) {
            $domainId = $domain->getId();

            $seoPageData->pageSlugsIndexedByDomainId[$domainId] = $this->generatePageSlug($domainId, $seoPage);

            $seoPageData->seoMetaDescriptionsIndexedByDomainId[$domainId] = $seoPage->getSeoMetaDescription($domainId);
            $seoPageData->seoTitlesIndexedByDomainId[$domainId] = $seoPage->getSeoTitle($domainId);
            $seoPageData->canonicalUrlsIndexedByDomainId[$domainId] = $seoPage->getCanonicalUrl($domainId);
            $seoPageData->seoOgTitlesIndexedByDomainId[$domainId] = $seoPage->getSeoOgTitle($domainId);
            $seoPageData->seoOgDescriptionsIndexedByDomainId[$domainId] = $seoPage->getSeoOgDescription($domainId);
            $seoPageData->seoOgImage = $this->imageUploadDataFactory->createFromEntityAndType($seoPage, SeoPageFacade::IMAGE_TYPE_OG);
        }

        $seoPageData->defaultPage = $seoPage->isDefaultPage();
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage $seoPage
     * @return string
     */
    protected function generatePageSlug(int $domainId, SeoPage $seoPage): string
    {
        $seoPageDomainRouter = $this->domainRouterFactory->getRouter($domainId);
        $friendlyUrl = $seoPageDomainRouter->generate('front_page_seo', [
            'id' => $seoPage->getId(),
        ]);

        return SeoPageSlugTransformer::transformFriendlyUrlToSeoPageSlug($friendlyUrl);
    }
}
