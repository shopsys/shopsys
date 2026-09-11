<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class SeoPageDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    public function create(): SeoPageData
    {
        $seoPageData = new SeoPageData();
        $seoPageData->seoOgImage = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            $seoPageData->seo[$domainId] = $this->seoAttributesDataFactory->create();
        }

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
            $seoPageData->seo[$domainId] = $this->seoAttributesDataFactory->createFromSeoAttributes(
                $seoPage->getSeoAttributes($domainId),
            );
            $seoPageData->seoOgTitlesIndexedByDomainId[$domainId] = $seoPage->getSeoOgTitle($domainId);
            $seoPageData->seoOgDescriptionsIndexedByDomainId[$domainId] = $seoPage->getSeoOgDescription($domainId);
            $seoPageData->seoOgImage = $this->imageUploadDataFactory->createFromEntityAndType($seoPage, SeoPageFacade::IMAGE_TYPE_OG);
        }

        $seoPageData->defaultPage = $seoPage->isDefaultPage();
    }
}
