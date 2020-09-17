<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Component\Domain\Domain;
use App\Component\Image\ImageFacade;
use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Exception\EntityIdentifierException;
use Shopsys\FrameworkBundle\Component\Setting\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueTypeNotMatchValueException;

class StockDataFactory
{
    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private $friendlyUrlFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        ImageFacade $imageFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain
    ) {
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
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
        $this->fillFromStock($stockData, $stock);

        return $stockData;
    }

    /**
     * @param StockData $stockData
     * @param Stock $stock
     */
    public function fillFromStock(StockData $stockData, Stock $stock): void
    {
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

        foreach ($this->domain->getAllIds() as $domainId) {
            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl($domainId, 'front_stores_detail', $stock->getId());
            $stockData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }
    }
}
