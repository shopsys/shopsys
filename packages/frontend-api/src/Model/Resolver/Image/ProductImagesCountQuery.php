<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductImagesCountQuery extends AbstractQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    public function __construct(protected readonly DataLoaderInterface $imagesCountBatchLoader)
    {
    }

    public function imagesCountByProductPromiseQuery(array $data, ?string $type): Promise
    {
        return $this->imagesCountBatchLoader->load(
            new ImageBatchLoadData(
                $data['id'],
                static::PRODUCT_ENTITY_NAME,
                $type,
            ),
        );
    }
}
