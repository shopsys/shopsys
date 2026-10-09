<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrontendApiBundle\Component\Files\FileBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductFilesQuery extends AbstractQuery
{
    protected const PRODUCT_ENTITY_NAME = 'product';

    public function __construct(
        protected readonly DataLoaderInterface $filesBatchLoader,
    ) {
    }

    public function filesByProductPromiseQuery(
        array $data,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): Promise {
        return $this->filesBatchLoader->load(
            new FileBatchLoadData(
                $data['id'],
                static::PRODUCT_ENTITY_NAME,
                $type,
            ),
        );
    }
}
