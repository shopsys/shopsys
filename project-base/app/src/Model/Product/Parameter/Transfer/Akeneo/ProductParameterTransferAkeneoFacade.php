<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;
use Generator;

class ProductParameterTransferAkeneoFacade
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
    public function getAllAttributes(): ?Generator
    {
        foreach ($this->akeneoClient->getAttributeApi()->all(self::PAGE_SIZE_LIMIT) as $itemAkeneoGroup) {
            yield $itemAkeneoGroup;
        }
    }

    /**
     * @param string $code
     * @return \Generator|null
     */
    public function getAttributeOptions(string $code): ?Generator
    {
        foreach ($this->akeneoClient->getAttributeOptionApi()->all($code, self::PAGE_SIZE_LIMIT) as $option) {
            yield $option;
        }
    }
}
