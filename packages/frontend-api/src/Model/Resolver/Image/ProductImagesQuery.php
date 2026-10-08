<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;

class ProductImagesQuery extends ImagesQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    public function imagesByProductPromiseQuery(
        array $data,
        ?string $type,
    ): Promise {
        return $this->resolveByEntityIdPromise($data['id'], static::PRODUCT_ENTITY_NAME, $type);
    }

    public function mainImageByProductPromiseQuery(
        array $data,
        ?string $type,
    ): Promise {
        return $this->mainImageByEntityIdPromiseQuery($data['id'], static::PRODUCT_ENTITY_NAME, $type);
    }
}
