<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\UrlListDataFactory;
use Shopsys\FrameworkBundle\Model\Seo\SeoAttributesDataFactory;

class BrandDataFactory
{
    public function __construct(
        protected readonly UrlListDataFactory $urlListDataFactory,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly SeoAttributesDataFactory $seoAttributesDataFactory,
    ) {
    }

    protected function createInstance(): BrandData
    {
        return new BrandData();
    }

    public function create(): BrandData
    {
        $brandData = $this->createInstance();
        $this->fillNew($brandData);

        return $brandData;
    }

    protected function fillNew(BrandData $brandData): void
    {
        $brandData->image = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seo[$domainId] = $this->seoAttributesDataFactory->create();
            $brandData->urls[$domainId] = $this->urlListDataFactory->create();
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $brandData->descriptions[$locale] = null;
        }
    }

    public function createFromBrand(Brand $brand): BrandData
    {
        $brandData = $this->createInstance();
        $this->fillFromBrand($brandData, $brand);

        return $brandData;
    }

    protected function fillFromBrand(BrandData $brandData, Brand $brand): void
    {
        $brandData->name = $brand->getName();

        $translations = $brand->getTranslations();

        $brandData->descriptions = [];

        foreach ($translations as $translation) {
            $brandData->descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        $brandData->urls = $this->urlListDataFactory->createForAllDomainsIndexedByDomainId('front_brand_detail', $brand->getId());

        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seo[$domainId] = $this->seoAttributesDataFactory->createFromSeoAttributes(
                $brand->getSeoAttributes($domainId),
            );
        }

        $brandData->image = $this->imageUploadDataFactory->createFromEntityAndType($brand);
    }
}
