<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Api\ProductApiInterface;
use Akeneo\Pim\ApiClient\Api\PublishedProductApiInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use DateTime;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;

class ProductTransferAkeneoFacade
{
    public const PAGE_SIZE_LIMIT = 50;
    public const API_AKENEO_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param \Akeneo\Pim\ApiClient\AkeneoPimClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimClientInterface $akeneoClient)
    {
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\PublishedProductApiInterface
     */
    private function getPublishedProductApi(): PublishedProductApiInterface
    {
        return $this->akeneoClient->getPublishedProductApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\ProductApiInterface
     */
    private function getProductApi(): ProductApiInterface
    {
        return $this->akeneoClient->getProductApi();
    }

    /**
     * @param string $identifier
     * @return array
     */
    public function getProductByIdentifier(string $identifier): array
    {
        return $this->getProductApi()->get($identifier);
    }

    /**
     * @param \DateTime $lastUpdatedProducts
     * @return \Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface
     */
    public function getAllUpdatedProductsFromLastUpdate(DateTime $lastUpdatedProducts): ResourceCursorInterface
    {
        $lastUpdatedProducts->setTimezone(new DateTimeZone('UTC'));

        $searchBuilder = new SearchBuilder();
        $searchBuilder->addFilter('updated', '>', $lastUpdatedProducts->format(self::API_AKENEO_DATETIME_FORMAT));
        $searchFilters = $searchBuilder->getFilters();

        return $this->getPublishedProductApi()->all(self::PAGE_SIZE_LIMIT, [
            'search' => $searchFilters,
        ]);
    }

    /**
     * @param string $akeneoMediaCode
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getProductMediaFileFromApi(string $akeneoMediaCode): ResponseInterface
    {
        return $this->akeneoClient->getProductMediaFileApi()->download($akeneoMediaCode);
    }
}
