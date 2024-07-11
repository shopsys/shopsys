<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductFilesQuery extends AbstractQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    /**
     * @param \Overblog\DataLoader\DataLoaderInterface $filesBatchLoader
     */
    public function __construct(
        protected readonly DataLoaderInterface $filesBatchLoader,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @param string $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function filesByProductPromiseQuery(
        $data,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): Promise {
        $productId = $data instanceof Product ? $data->getId() : $data['id'];

        return $this->resolveByEntityIdPromise($productId, static::PRODUCT_ENTITY_NAME, $type);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function resolveByEntityIdPromise(
        int $entityId,
        string $entityName,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): Promise {
        return $this->filesBatchLoader->load(
            new FileBatchLoadData(
                $entityId,
                $entityName,
                $type,
            ),
        );
    }
}
