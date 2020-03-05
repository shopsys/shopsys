<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\Api\CategoryApiInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;

class CategoryTransferAkeneoFacade
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
     * @return \Akeneo\Pim\ApiClient\Api\CategoryApiInterface
     */
    private function getCategoryFromApi(): CategoryApiInterface
    {
        return $this->akeneoClient->getCategoryApi();
    }

    /**
     * @return \Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface
     */
    public function getAllCategories(): ResourceCursorInterface
    {
        return $this->getCategoryFromApi()->all(self::PAGE_SIZE_LIMIT);
    }
}
