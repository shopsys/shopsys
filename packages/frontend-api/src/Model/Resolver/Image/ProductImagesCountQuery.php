<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductImagesCountQuery extends AbstractQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    public function __construct(protected readonly DataLoaderInterface $imagesCountBatchLoader)
    {
    }

    public function imagesCountByProductPromiseQuery(Product|array $data, ?string $type): Promise
    {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->imagesCountBatchLoader->load(
            new ImageBatchLoadData(
                $productId,
                static::PRODUCT_ENTITY_NAME,
                $type,
            ),
        );
    }
}
