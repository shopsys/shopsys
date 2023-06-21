<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Generator;

class ProductParameterGroupTransferAkeneoFacade
{
    private const PAGE_SIZE_LIMIT = 50;

    /**
     * @param \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimEnterpriseClientInterface $akeneoClient)
    {
    }

    /**
     * @return \Generator|null
     */
    public function getAllAttributesGroup(): ?Generator
    {
        foreach ($this->akeneoClient->getAttributeGroupApi()->all(self::PAGE_SIZE_LIMIT) as $itemAkeneoGroup) {
            yield $itemAkeneoGroup;
        }
    }
}
