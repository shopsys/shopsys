<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductImagesResolver extends ImagesResolver implements AliasedInterface
{
    private const PRODUCT_ENTITY_NAME = 'product';

    /**
     * @param \App\Model\Product\Product|array $data
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveImagesByProduct($data, ?string $type, ?array $sizes): Promise
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];
        return $this->resolveByEntityId($productId, self::PRODUCT_ENTITY_NAME, $type, $sizes);
    }

    /**
     * @param \App\Model\Product\Product|array $data
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveMainImageByProduct($data, ?string $type, ?string $size): Promise
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];
        $sizes = $size === null ? [] : [$size];
        $sizeConfigs = $this->getSizeConfigs($type, $sizes, self::PRODUCT_ENTITY_NAME);

        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $productId,
                self::PRODUCT_ENTITY_NAME,
                $sizeConfigs,
                $type
            )
        );
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveImagesByProduct' => 'resolveImagesByProduct',
            'resolveMainImageByProduct' => 'resolveMainImageByProduct',
        ];
    }
}
