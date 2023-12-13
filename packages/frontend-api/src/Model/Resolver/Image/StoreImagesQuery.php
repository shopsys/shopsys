<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Store\Store;

class StoreImagesQuery extends ImagesQuery
{
    protected const STORE_ENTITY_NAME = 'store';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store|array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByStorePromiseQuery(Store|array $data, ?string $type): Promise
    {
        $storeId = $data instanceof Store ? $data->getId() : $data['id'];

        return $this->resolveByEntityIdPromise($storeId, static::STORE_ENTITY_NAME, $type);
    }
}
