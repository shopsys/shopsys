<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;

class ProductImagesQuery extends ImagesQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    public function imagesByProductPromiseQuery(
        Product|array $data,
        ?string $type,
    ): Promise {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->resolveByEntityIdPromise($productId, static::PRODUCT_ENTITY_NAME, $type);
    }

    public function mainImageByProductPromiseQuery(
        Product|array $data,
        ?string $type,
    ): Promise {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $productId,
                static::PRODUCT_ENTITY_NAME,
                $type,
            ),
        );
    }
}
