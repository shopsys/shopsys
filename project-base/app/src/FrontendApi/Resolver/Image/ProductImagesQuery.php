<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductImagesQuery extends ImagesQuery
{
    private const PRODUCT_ENTITY_NAME = 'product';

    /**
     * @param \App\Model\Product\Product|array $data
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByProductPromiseQuery($data, ?string $type, ?array $sizes): Promise
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];
        return $this->resolveByEntityIdPromise($productId, self::PRODUCT_ENTITY_NAME, $type, $sizes);
    }

    /**
     * @param \App\Model\Product\Product|array $data
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByProductPromiseQuery($data, ?string $type, ?string $size): Promise
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];
        $sizes = $size === null ? [] : [$size];
        $sizeConfigs = $this->getSizesConfigs($type, $sizes, self::PRODUCT_ENTITY_NAME);

        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $productId,
                self::PRODUCT_ENTITY_NAME,
                $sizeConfigs,
                $type,
            ),
        );
    }
}
