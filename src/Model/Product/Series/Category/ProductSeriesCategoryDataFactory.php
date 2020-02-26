<?php

declare(strict_types=1);


namespace App\Model\Product\Series\Category;


use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ProductSeriesCategoryDataFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @return \App\Model\Product\Series\Category\ProductSeriesCategoryData
     */
    public function create(): ProductSeriesCategoryData
    {
        return new ProductSeriesCategoryData();
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory $productSeriesCategory
     * @return \App\Model\Product\Series\Category\ProductSeriesCategoryData
     */
    public function createFromProductSeriesCategory(ProductSeriesCategory $productSeriesCategory): ProductSeriesCategoryData
    {
        $productSeriesCategoryData = $this->create();

        foreach($this->domain->getAll() as $domainConfig){
            $domainId = $domainConfig->getId();
            $productSeriesCategoryData->seoH1[$domainId] = $productSeriesCategory->getSeoH1($domainId);
            $productSeriesCategoryData->seoMetaDescription[$domainId] = $productSeriesCategory->getSeoMetaDescription($domainId);
            $productSeriesCategoryData->seoTitle[$domainId] = $productSeriesCategory->getSeoTitle($domainId);
        }

        /** @var \App\Model\Product\Series\ProductSeriesTranslation[] $translations */
        $translations = $productSeriesCategory->getTranslations();
        foreach ($translations as $translation) {
            $productSeriesCategoryData->name[$translation->getLocale()] = $translation->getName();
            $productSeriesCategoryData->description[$translation->getLocale()] = $translation->getDescription();
        }

        return $productSeriesCategoryData;
    }
}