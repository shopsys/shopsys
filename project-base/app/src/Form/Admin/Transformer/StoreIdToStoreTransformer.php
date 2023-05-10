<?php

declare(strict_types=1);

namespace App\Form\Admin\Transformer;

use App\Model\Store\Exception\StoreNotFoundException;
use App\Model\Store\Store;
use App\Model\Store\StoreRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StoreIdToStoreTransformer implements DataTransformerInterface
{
    /**
     * @var \App\Model\Store\StoreRepository
     */
    private StoreRepository $storeRepository;

    /**
     * @param \App\Model\Store\StoreRepository $storeRepository
     */
    public function __construct(StoreRepository $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param \App\Model\Store\Store|null $store
     * @return int|null
     */
    public function transform($store): ?int
    {
        return $store !== null ? $store->getId() : null;
    }

    /**
     * @param int|null $storeId
     * @return \App\Model\Store\Store|null
     */
    public function reverseTransform($storeId): ?Store
    {
        if ($storeId === null) {
            return null;
        }

        try {
            return $this->storeRepository->getById((int)$storeId);
        } catch (StoreNotFoundException $e) {
            throw new TransformationFailedException('Store not found', 0, $e);
        }
    }
}
