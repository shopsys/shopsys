<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

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
        private readonly Domain $domain,
        private readonly DomainRouterFactory $domainRouterFactory,
        private readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \App\Model\SeoPage\SeoPageData
     */
    public function create(): SeoPageData
    {
        $seoPageData = new SeoPageData();
        $seoPageData->seoOgImage = $this->imageUploadDataFactory->create();

        return $seoPageData;
    }

    /**
     * @param \App\Model\SeoPage\SeoPage $seoPage
     * @return \App\Model\SeoPage\SeoPageData
     */
    public function createFromSeoPage(SeoPage $seoPage): SeoPageData
    {
        $seoPageData = $this->create();
        $this->fillFromSeoPage($seoPageData, $seoPage);

        return $seoPageData;
    }

    /**
     * @param \App\Model\SeoPage\SeoPageData $seoPageData
     * @param \App\Model\SeoPage\SeoPage $seoPage
     */
    private function fillFromSeoPage(SeoPageData $seoPageData, SeoPage $seoPage): void
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
     * @param \App\Model\SeoPage\SeoPage $seoPage
     * @return string
     */
    private function generatePageSlug(int $domainId, SeoPage $seoPage): string
    {
        $seoPageDomainRouter = $this->domainRouterFactory->getRouter($domainId);
        $friendlyUrl = $seoPageDomainRouter->generate('front_page_seo', [
            'id' => $seoPage->getId(),
        ]);

        return SeoPageSlugTransformer::transformFriendlyUrlToSeoPageSlug($friendlyUrl);
    }
}
