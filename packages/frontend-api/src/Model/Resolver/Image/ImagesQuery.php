<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ImagesQuery extends AbstractQuery
{
    public function __construct(
        protected readonly ImageConfig $imageConfig,
        protected readonly DataLoaderInterface $imagesBatchLoader,
        protected readonly DataLoaderInterface $firstImageBatchLoader,
    ) {
    }

    public function mainImageByEntityPromiseQuery(object $entity, ?string $type): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->mainImageByEntityIdPromiseQuery($entity->getId(), $imageEntityConfig->getEntityName(), $type);
    }

    public function mainImageByEntityIdPromiseQuery(
        int $entityId,
        string $entityName,
        ?string $type,
    ): Promise {
        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $entityId,
                $entityName,
                $type,
            ),
        );
    }

    public function imagesByEntityPromiseQuery(object $entity, ?string $type): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->resolveByEntityIdPromise($entity->getId(), $imageEntityConfig->getEntityName(), $type);
    }

    protected function resolveByEntityIdPromise(
        int $entityId,
        string $entityName,
        ?string $type,
    ): Promise {
        return $this->imagesBatchLoader->load(
            new ImageBatchLoadData(
                $entityId,
                $entityName,
                $type,
            ),
        );
    }
}
