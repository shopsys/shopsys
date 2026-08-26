<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class SeoPageDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    public function create(): SeoPageData
    {
        $seoPageData = new SeoPageData();
        $seoPageData->seoOgImage = $this->imageUploadDataFactory->create();

        return $seoPageData;
    }

    public function createFromSeoPage(SeoPage $seoPage): SeoPageData
    {
        $seoPageData = $this->create();
        $this->fillFromSeoPage($seoPageData, $seoPage);

        return $seoPageData;
    }

    protected function fillFromSeoPage(SeoPageData $seoPageData, SeoPage $seoPage): void
    {
        $seoPageData->pageName = $seoPage->getPageName();

        foreach ($this->domain->getAll() as $domain) {
            $domainId = $domain->getId();

            $seoPageData->pageSlugsIndexedByDomainId[$domainId] = $seoPage->getPageSlug($domainId);
            $seoPageData->seoMetaDescriptionsIndexedByDomainId[$domainId] = $seoPage->getSeoMetaDescription($domainId);
            $seoPageData->seoTitlesIndexedByDomainId[$domainId] = $seoPage->getSeoTitle($domainId);
            $seoPageData->canonicalUrlsIndexedByDomainId[$domainId] = $seoPage->getCanonicalUrl($domainId);
            $seoPageData->seoOgTitlesIndexedByDomainId[$domainId] = $seoPage->getSeoOgTitle($domainId);
            $seoPageData->seoOgDescriptionsIndexedByDomainId[$domainId] = $seoPage->getSeoOgDescription($domainId);
            $seoPageData->seoOgImage = $this->imageUploadDataFactory->createFromEntityAndType($seoPage, SeoPageFacade::IMAGE_TYPE_OG);
        }

        $seoPageData->defaultPage = $seoPage->isDefaultPage();
    }
}
