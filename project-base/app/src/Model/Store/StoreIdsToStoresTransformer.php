<?php

declare(strict_types=1);

namespace App\Model\Store;

use Symfony\Component\Form\DataTransformerInterface;

class StoreIdsToStoresTransformer implements DataTransformerInterface
{
    /**
     * @param \App\Model\Store\StoreRepository $storeRepository
     */
    public function __construct(private readonly StoreRepository $storeRepository)
    {
    }

    /**
     * @param \App\Model\Store\Store[]|null $stores
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
     * @return \App\Model\Store\Store[]|null
     */
    public function reverseTransform($storeIds): ?array
    {
        if ($storeIds === null) {
            return null;
        }

        return $this->storeRepository->getStoresByIds($storeIds);
    }
}
