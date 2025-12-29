<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Generator;

class ProductParameterGroupTransferAkeneoFacade
{
    private const PAGE_SIZE_LIMIT = 50;

    /**
     * @param \Akeneo\Pim\ApiClient\AkeneoPimClientInterface $akeneoClient
     */
    public function __construct(private AkeneoPimClientInterface $akeneoClient)
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
