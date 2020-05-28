<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\Api\FamilyVariantApiInterface;
use Akeneo\Pim\ApiClient\Api\ProductApiInterface;
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
    private function getPublishedProductApi(): PublishedProductApiInterface
    {
        return $this->akeneoClient->getPublishedProductApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\ProductModelApiInterface
     */
    private function getProductModelApi(): ProductModelApiInterface
    {
        return $this->akeneoClient->getProductModelApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\FamilyVariantApiInterface
     */
    private function getFamilyVariantApi(): FamilyVariantApiInterface
    {
        return $this->akeneoClient->getFamilyVariantApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Api\ProductApiInterface
     */
    private function getProductApi(): ProductApiInterface
    {
        return $this->akeneoClient->getProductApi();
    }

    /**
     * @param string $code
     * @return array
     */
    public function getProductModelByCode(string $code): array
    {
        return $this->getProductModelApi()->get($code);
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
     * @param string $familyCode
     * @param string $familyVariantCode
     * @return array
     */
    public function getFamilyVariant(string $familyCode, string $familyVariantCode): array
    {
        return $this->getFamilyVariantApi()->get($familyCode, $familyVariantCode);
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

        $publishedProducts = $this->getPublishedProductApi()->all(self::PAGE_SIZE_LIMIT, [
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
