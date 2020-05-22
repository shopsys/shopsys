<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\Api\ProductModelApiInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Akeneo\PimEnterprise\ApiClient\Api\PublishedProductApiInterface;
use DateTime;
use Psr\Http\Message\ResponseInterface;

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
     * @return \Akeneo\Pim\ApiClient\Api\ProductModelApiInterface
     */
    private function getProductModelFromApi(): ProductModelApiInterface
    {
        return $this->akeneoClient->getProductModelApi();
    }

    /**
     * @param string $code
     * @return array
     */
    public function getProductModelByCode(string $code): array
    {
        return $this->getProductModelFromApi()->get($code);
    }

    /**
     * @param \DateTime $lastUpdatedProducts
     * @return \Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface
     */
    public function getAllUpdatedProductsFromLastUpdate(DateTime $lastUpdatedProducts): ResourceCursorInterface
    {
        $lastUpdatedProducts->setTimezone(new \DateTimeZone('UTC'));

        $searchBuilder = new SearchBuilder();
        $searchBuilder->addFilter('updated', '>', $lastUpdatedProducts->format(self::API_AKENEO_DATETIME_FORMAT));
        $searchFilters = $searchBuilder->getFilters();

        $publishedProducts = $this->getPublishedProductFromApi()->all(self::PAGE_SIZE_LIMIT, [
            'search' => $searchFilters,
        ]);

        return $publishedProducts;
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
