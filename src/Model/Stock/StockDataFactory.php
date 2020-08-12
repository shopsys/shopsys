<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Component\Image\ImageFacade;

class StockDataFactory
{
    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(ImageFacade $imageFacade)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @return \App\Model\Stock\StockData
     */
    public function create(): StockData
    {
        return new StockData();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockData
     */
    public function createFromStock(Stock $stock): StockData
    {
        $stockData = new StockData();
        $stockData->name = $stock->getName();
        $stockData->domainId = $stock->getDomainId();
        $stockData->centralStock = $stock->isCentralStock();
        $stockData->externalId = $stock->getExternalId();
        $stockData->street = $stock->getStreet();
        $stockData->city = $stock->getCity();
        $stockData->openingHours = $stock->getOpeningHours();
        $stockData->extraordinaryOpeningHours = $stock->getExtraordinaryOpeningHours();
        $stockData->contactText1 = $stock->getContactText1();
        $stockData->contactText2 = $stock->getContactText2();
        $stockData->contactInfo = $stock->getContactInfo();
        $stockData->locationLat = $stock->getLocationLat();
        $stockData->locationLng = $stock->getLocationLng();

        $stockData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($stock, 'main');
        $stockData->imageGallery->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($stock, 'gallery');

        return $stockData;
    }
}
