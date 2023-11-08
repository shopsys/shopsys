<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\Component\Deprecation\DeprecatedMethodException;
use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Image\ImagesQuery as BaseImagesQuery;

class ImagesQuery extends BaseImagesQuery
{
    /**
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Image\ImageFacade $frontendApiImageFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $imagesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $firstImageBatchLoader
     */
    public function __construct(
        ImageFacade $imageFacade,
        ImageConfig $imageConfig,
        Domain $domain,
        FrontendApiImageFacade $frontendApiImageFacade,
        protected readonly DataLoaderInterface $imagesBatchLoader,
        protected readonly DataLoaderInterface $firstImageBatchLoader,
    ) {
        parent::__construct($imageFacade, $imageConfig, $domain, $frontendApiImageFacade);
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

    /**
     * @deprecated Method is deprecated. Use "AdvertImagesQuery::imagesByAdvertPromiseQuery()" instead.
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @return array
     */
    public function imagesByAdvertQuery(Advert $advert, ?string $type): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. Use "ProductImagesQuery::imagesByProductPromiseQuery()" instead.
     * @param $data
     * @param string|null $type
     * @return array
     */
    public function imagesByProductQuery($data, ?string $type): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. Use "imagesByEntityPromise()" instead.
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @return array
     */
    protected function resolveByEntityId(int $entityId, string $entityName, ?string $type): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. With Promises will be unused.
     * @param array $images
     * @return array<int, array{url: string, name: string|null}>
     */
    protected function getResolvedImages(array $images): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Component\Image\Image $image
     * @return array{url: string, name: string|null}
     */
    protected function getResolvedImage(Image $image): array
    {
        throw new DeprecatedMethodException();
    }
}
