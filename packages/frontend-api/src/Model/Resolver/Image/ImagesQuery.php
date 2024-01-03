<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrontendApiBundle\Component\Image\ImageApiFacade;
use Shopsys\FrontendApiBundle\Component\Image\ImageBatchLoadData;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ImagesQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageApiFacade $imageApiFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $imagesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $firstImageBatchLoader
     */
    public function __construct(
        protected readonly ImageFacade $imageFacade,
        protected readonly ImageConfig $imageConfig,
        protected readonly Domain $domain,
        protected readonly ImageApiFacade $imageApiFacade,
        protected readonly DataLoaderInterface $imagesBatchLoader,
        protected readonly DataLoaderInterface $firstImageBatchLoader,
    ) {
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByEntityPromiseQuery(object $entity, ?string $type): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->mainImageByEntityIdPromiseQuery($entity->getId(), $imageEntityConfig->getEntityName(), $type);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
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

    /**
     * @param object $entity
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByEntityPromiseQuery(object $entity, ?string $type): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->resolveByEntityIdPromise($entity->getId(), $imageEntityConfig->getEntityName(), $type);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return \GraphQL\Executor\Promise\Promise
     */
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
