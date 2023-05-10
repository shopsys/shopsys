<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\Component\Deprecation\DeprecatedMethodException;
use App\FrontendApi\Model\Image\ImageBatchLoadData;
use App\FrontendApi\Resolver\Image\Exception\ImageSizeInvalidUserError;
use App\FrontendApi\Resolver\Image\Exception\ImageTypeInvalidUserError;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
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
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $imagesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $firstImageBatchLoader
     */
    public function __construct(
        ImageFacade $imageFacade,
        ImageConfig $imageConfig,
        Domain $domain,
        FrontendApiImageFacade $frontendApiImageFacade,
        protected readonly DataLoaderInterface $imagesBatchLoader,
        protected readonly DataLoaderInterface $firstImageBatchLoader
    ) {
        parent::__construct($imageFacade, $imageConfig, $domain, $frontendApiImageFacade);
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByEntityPromiseQuery(object $entity, ?string $type, ?string $size): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->mainImageByEntityIdPromiseQuery($entity->getId(), $imageEntityConfig->getEntityName(), $type, $size);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function mainImageByEntityIdPromiseQuery(int $entityId, string $entityName, ?string $type, ?string $size): Promise
    {
        $sizes = $size === null ? [] : [$size];
        $sizeConfigs = $this->getSizesConfigs($type, $sizes, $entityName);

        return $this->firstImageBatchLoader->load(
            new ImageBatchLoadData(
                $entityId,
                $entityName,
                $sizeConfigs,
                $type
            )
        );
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function imagesByEntityPromiseQuery(object $entity, ?string $type, ?array $sizes): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->resolveByEntityIdPromise($entity->getId(), $imageEntityConfig->getEntityName(), $type, $sizes);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function resolveByEntityIdPromise(int $entityId, string $entityName, ?string $type, ?array $sizes): Promise
    {
        $sizeConfigs = $this->getSizesConfigs($type, $sizes, $entityName);

        return $this->imagesBatchLoader->load(
            new ImageBatchLoadData(
                $entityId,
                $entityName,
                $sizeConfigs,
                $type
            )
        );
    }

    /**
     * @param string|null $type
     * @param array|null $sizes
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function getSizesConfigs(?string $type, ?array $sizes, string $entityName): array
    {
        $imageConfig = $this->imageConfig->getEntityConfigByEntityName($entityName);

        if ($sizes === []) {
            $sizes = null;
        }

        if ($sizes === null && $type === null) {
            return $imageConfig->getSizeConfigs();
        }

        if ($sizes === null) {
            try {
                return $imageConfig->getSizeConfigsByType($type);
            } catch (ImageTypeNotFoundException $e) {
                throw new ImageTypeInvalidUserError(sprintf('Image type "%s" not found for %s', $type, $entityName));
            }
        }

        $imageSizeConfigs = [];
        foreach ($sizes as $size) {
            $imageSizeConfigs[] = $this->getSingleSizeConfig($imageConfig, $type, $size, $entityName);
        }

        return $imageSizeConfigs;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig $imageConfig
     * @param string|null $type
     * @param string $size
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    private function getSingleSizeConfig(ImageEntityConfig $imageConfig, ?string $type, string $size, string $entityName): ImageSizeConfig
    {
        try {
            if ($size === ImageConfig::DEFAULT_SIZE_NAME) {
                $size = ImageEntityConfig::WITHOUT_NAME_KEY;
            }

            if ($type === null) {
                return $imageConfig->getSizeConfig($size);
            }

            return $imageConfig->getSizeConfigByType($type, $size);
        } catch (ImageSizeNotFoundException $e) {
            throw new ImageSizeInvalidUserError(sprintf('Image size "%s" not found for %s', $size, $entityName));
        } catch (ImageTypeNotFoundException $e) {
            throw new ImageTypeInvalidUserError(sprintf('Image type "%s" not found for %s', $type, $entityName));
        }
    }

    /**
     * @deprecated Method is deprecated. Use "AdvertImagesQuery::imagesByAdvertPromiseQuery()" instead.
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function imagesByAdvertQuery(Advert $advert, ?string $type, ?string $size): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. Use "ProductImagesQuery::imagesByProductPromiseQuery()" instead.
     * @param $data
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    public function imagesByProductQuery($data, ?string $type, ?string $size): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. Use "imagesByEntityPromiseQuery()" instead.
     * @param object $entity
     * @param string|null $type
     * @param string|null $size
     * @throws \App\Component\Deprecation\DeprecatedMethodException
     * @return array
     */
    public function imagesByEntityQuery(object $entity, ?string $type, ?string $size): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. Use "imagesByEntityPromise()" instead.
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param string|null $size
     * @return array
     */
    protected function resolveByEntityId(int $entityId, string $entityName, ?string $type, ?string $size): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. With Promises will be unused.
     * @param array $images
     * @param array $sizeConfigs
     * @throws \App\Component\Deprecation\DeprecatedMethodException
     * @return array
     */
    protected function getResolvedImages(array $images, array $sizeConfigs): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. With Promises will be unused.
     * @param \App\Component\Image\Image $image
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig $sizeConfig
     * @throws \App\Component\Deprecation\DeprecatedMethodException
     * @return array
     */
    protected function getResolvedImage(Image $image, ImageSizeConfig $sizeConfig): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. With Promises will be unused.
     * @param string|null $type
     * @param string|null $size
     * @param string $entityName
     * @throws \App\Component\Deprecation\DeprecatedMethodException
     * @return array|\Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function getSizeConfigs(?string $type, ?string $size, string $entityName): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @deprecated Method is deprecated. With Promises will be unused.
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param string|null $size
     * @throws \App\Component\Deprecation\DeprecatedMethodException
     * @return array|\Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function getSizeConfigsForAdvert(Advert $advert, ?string $type, ?string $size): array
    {
        throw new DeprecatedMethodException();
    }
}
