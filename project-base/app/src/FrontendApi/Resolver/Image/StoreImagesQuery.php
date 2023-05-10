<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\Model\Store\Store;
use GraphQL\Executor\Promise\Promise;

final class StoreImagesQuery extends ImagesQuery
{
    private const STORE_ENTITY_NAME = 'store';

    /**
     * @param \App\Model\Store\Store|array $data
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByStorePromiseQuery(Store|array $data, ?string $type, ?array $sizes): Promise
    {
        $storeId = $data instanceof Store ? $data->getId() : $data['id'];

        return $this->resolveByEntityIdPromise($storeId, self::STORE_ENTITY_NAME, $type, $sizes);
    }
}
