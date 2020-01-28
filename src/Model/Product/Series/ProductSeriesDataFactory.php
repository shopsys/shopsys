<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ProductSeriesDataFactory implements ProductSeriesDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        Domain $domain,
        ImageFacade $imageFacade,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @return \App\Model\Product\Series\ProductSeriesData
     */
    public function create(): ProductSeriesData
    {
        $productSeriesData = new ProductSeriesData();
        $this->fillDefaultData($productSeriesData);
        return $productSeriesData;
    }

    /**
     * @param \App\Model\Product\Series\ProductSeriesData $productSeriesData
     */
    private function fillDefaultData(ProductSeriesData $productSeriesData)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productSeriesData->hidden[$domainId] = true;
        }
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \App\Model\Product\Series\ProductSeriesData
     */
    public function createFromProductSeries(ProductSeries $productSeries): ProductSeriesData
    {
        $productSeriesData = $this->create();

        /** @var \App\Model\Product\Series\ProductSeriesTranslation[] $translations */
        $translations = $productSeries->getTranslations();
        foreach ($translations as $translation) {
            $productSeriesData->names[$translation->getLocale()] = $translation->getName();
            $productSeriesData->descriptions[$translation->getLocale()] = $translation->getDescription();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $productSeriesData->seoH1s[$domainId] = $productSeries->getSeoH1($domainId);
            $productSeriesData->seoTitles[$domainId] = $productSeries->getSeoTitle($domainId);
            $productSeriesData->seoMetaDescriptions[$domainId] = $productSeries->getSeoMetaDescription($domainId);
            $productSeriesData->hidden[$domainId] = $productSeries->isHidden($domainId);

            $productSeriesData->urls->mainFriendlyUrlsByDomainId[$domainId] =
                $this->friendlyUrlFacade->findMainFriendlyUrl(
                    $domainId,
                    'front_productseries_detail',
                    $productSeries->getId()
                );
        }

        $productSeriesData->images->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($productSeries, 'images');
        $productSeriesData->mainImage->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($productSeries, 'mainImage');

        return $productSeriesData;
    }
}
