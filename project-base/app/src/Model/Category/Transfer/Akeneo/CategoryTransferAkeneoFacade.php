<?php

declare(strict_types=1);

namespace App\Model\Category\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Api\CategoryApiInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;

class CategoryTransferAkeneoFacade
{
    public const PAGE_SIZE_LIMIT = 50;

    /**
     * @param \Akeneo\Pim\ApiClient\AkeneoPimClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimClientInterface $akeneoClient)
    {
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
