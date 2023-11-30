<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Symfony\Component\Form\DataTransformerInterface;

class StoreIdsToStoresTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreRepository $storeRepository
     */
    public function __construct(private readonly StoreRepository $storeRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store[]|null $stores
     * @return int[]
     */
    public function transform($stores): ?array
    {
        if ($stores === null) {
            return null;
        }

        return array_map(static fn (Store $store): int => $store->getId(), $stores);
    }

    /**
     * @param int[] $storeIds
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]|null
     */
    public function reverseTransform($storeIds): ?array
    {
        if ($storeIds === null) {
            return null;
        }

        return $this->storeRepository->getStoresByIds($storeIds);
    }
}
