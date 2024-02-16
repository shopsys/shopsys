<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;

class ProductImagesQuery extends ImagesQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByProductPromiseQuery($data, ?string $type): Promise
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->resolveByEntityIdPromise($productId, static::PRODUCT_ENTITY_NAME, $type);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByProductPromiseQuery($data, ?string $type): Promise
    {
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
