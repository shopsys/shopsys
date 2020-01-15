<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Akeneo\PimEnterprise\ApiClient\Api\PublishedProductApiInterface;

class ProductTransferAkeneoFacade
{
    public const PAGE_SIZE_LIMIT = 50;

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
     * @return \Generator|null
     */
    public function getAll(): ?\Generator
    {
        $publishedProducts = $this->getPublishedProductFromApi()->all(self::PAGE_SIZE_LIMIT);

        foreach ($publishedProducts as $product) {
            yield $product;
        }
    }
}
