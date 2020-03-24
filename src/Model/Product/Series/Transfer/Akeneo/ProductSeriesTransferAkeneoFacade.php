<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Akeneo\PimEnterprise\ApiClient\Api\ReferenceEntityRecordApiInterface;

class ProductSeriesTransferAkeneoFacade
{
    public const PRODUCT_SERIES_ENTITY_CODE = 'product_series';

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
     * @return \Akeneo\PimEnterprise\ApiClient\Api\ReferenceEntityRecordApiInterface
     */
    private function getEntityRecordsFromApi(): ReferenceEntityRecordApiInterface
    {
        return $this->akeneoClient->getReferenceEntityRecordApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface
     */
    public function getAllProductSeries(): ResourceCursorInterface
    {
        return $this->getEntityRecordsFromApi()->all(self::PRODUCT_SERIES_ENTITY_CODE);
    }
}
