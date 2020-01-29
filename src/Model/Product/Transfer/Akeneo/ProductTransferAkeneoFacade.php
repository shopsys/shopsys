<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Akeneo\PimEnterprise\ApiClient\Api\PublishedProductApiInterface;
use DateTime;

class ProductTransferAkeneoFacade
{
    public const PAGE_SIZE_LIMIT = 50;
    public const API_AKENEO_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface
     */
    private $akeneoClient;

    /**
     * @param \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface $akeneoClient
     */
    public function __construct(AkeneoPimEnterpriseClientInterface $akeneoClient)
    {
        $this->akeneoClient = $akeneoClient;
    }

    /**
     * @return \Akeneo\PimEnterprise\ApiClient\Api\PublishedProductApiInterface
     */
    private function getPublishedProductFromApi(): PublishedProductApiInterface
    {
        return $this->akeneoClient->getPublishedProductApi();
    }

    /**
     * @param \DateTime $lastUpdatedProducts
     * @return \Generator|null
     */
    public function getAllUpdatedProductsFromLastUpdate(DateTime $lastUpdatedProducts): ?\Generator
    {
        $lastUpdatedProducts->setTimezone(new \DateTimeZone('UTC'));

        $searchBuilder = new SearchBuilder();
        $searchBuilder->addFilter('updated', '>', $lastUpdatedProducts->format(self::API_AKENEO_DATETIME_FORMAT));
        $searchFilters = $searchBuilder->getFilters();

        $publishedProducts = $this->getPublishedProductFromApi()->all(self::PAGE_SIZE_LIMIT, [
            'search' => $searchFilters,
        ]);

        foreach ($publishedProducts as $product) {
            yield $product;
        }
    }
}
