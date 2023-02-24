<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class BrandDataFactory implements BrandDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BrandFacade $brandFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    protected function createInstance(): BrandData
    {
        $brandData = new BrandData();
        $brandData->image = $this->imageUploadDataFactory->create();

        return $brandData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    public function create(): BrandData
    {
        $brandData = $this->createInstance();
        $this->fillNew($brandData);

        return $brandData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     */
    protected function fillNew(BrandData $brandData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seoMetaDescriptions[$domainId] = null;
            $brandData->seoTitles[$domainId] = null;
            $brandData->seoH1s[$domainId] = null;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $brandData->descriptions[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData
     */
    public function createFromBrand(Brand $brand): BrandData
    {
        $brandData = $this->createInstance();
        $this->fillFromBrand($brandData, $brand);

        return $brandData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     */
    protected function fillFromBrand(BrandData $brandData, Brand $brand): void
    {
        $brandData->name = $brand->getName();

        /** @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandTranslation[] $translations */
        $translations = $brand->getTranslations();

        $brandData->descriptions = [];
        foreach ($translations as $translation) {
            $brandData->descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $brandData->seoH1s[$domainId] = $brand->getSeoH1($domainId);
            $brandData->seoTitles[$domainId] = $brand->getSeoTitle($domainId);
            $brandData->seoMetaDescriptions[$domainId] = $brand->getSeoMetaDescription($domainId);

            $brandData->urls->mainFriendlyUrlsByDomainId[$domainId] =
                $this->friendlyUrlFacade->findMainFriendlyUrl(
                    $domainId,
                    'front_brand_detail',
                    $brand->getId()
                );
        }

        $brandData->image = $this->imageUploadDataFactory->createFromEntityAndType($brand);
    }
}
