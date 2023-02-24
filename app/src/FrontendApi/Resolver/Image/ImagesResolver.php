<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use App\FrontendApi\Resolver\Image\Exception\ImageSizeInvalidUserError;
use App\FrontendApi\Resolver\Image\Exception\ImageTypeInvalidUserError;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;

class ImagesResolver implements QueryInterface, AliasedInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $imagesBatchLoader
     * @param \Overblog\DataLoader\DataLoaderInterface $firstImageBatchLoader
     */
    public function __construct(
        protected readonly ImageConfig $imageConfig,
        protected readonly FrontendApiImageFacade $frontendApiImageFacade,
        protected readonly DataLoaderInterface $imagesBatchLoader,
        protected readonly DataLoaderInterface $firstImageBatchLoader
    ) {
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveMainImageByEntity(object $entity, ?string $type, ?string $size): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->resolveMainImageByEntityId($entity->getId(), $imageEntityConfig->getEntityName(), $type, $size);
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param string|null $size
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveMainImageByEntityId(int $entityId, string $entityName, ?string $type, ?string $size): Promise
    {
        $sizes = $size === null ? [] : [$size];
        $sizeConfigs = $this->getSizeConfigs($type, $sizes, $entityName);

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
    public function resolveImagesByEntity(object $entity, ?string $type, ?array $sizes): Promise
    {
        $imageEntityConfig = $this->imageConfig->getImageEntityConfig($entity);

        return $this->resolveByEntityId($entity->getId(), $imageEntityConfig->getEntityName(), $type, $sizes);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolveImagesByEntity' => 'resolveImagesByEntity',
            'resolveMainImageByEntity' => 'resolveMainImageByEntity',
        ];
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function resolveByEntityId(int $entityId, string $entityName, ?string $type, ?array $sizes): Promise
    {
        $sizeConfigs = $this->getSizeConfigs($type, $sizes, $entityName);

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
    protected function getSizeConfigs(?string $type, ?array $sizes, string $entityName): array
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
}
