<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;
use Shopsys\FrontendApiBundle\Component\Image\ImageFacade as FrontendApiImageFacade;

abstract class AbstractImagesResolver implements ResolverInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private ImageConfig $imageConfig;

    /**
     * @var \Shopsys\FrontendApiBundle\Component\Image\ImageFacade
     */
    protected FrontendApiImageFacade $frontendApiImageFacade;

    /**
     * @var string
     */
    protected static string $entityName;

    /**
     * @var \Overblog\DataLoader\DataLoaderInterface
     */
    protected DataLoaderInterface $imagesBatchLoader;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \Shopsys\FrontendApiBundle\Component\Image\ImageFacade $frontendApiImageFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $imagesBatchLoader
     */
    public function __construct(
        ImageConfig $imageConfig,
        FrontendApiImageFacade $frontendApiImageFacade,
        DataLoaderInterface $imagesBatchLoader
    ) {
        $this->imageConfig = $imageConfig;
        $this->frontendApiImageFacade = $frontendApiImageFacade;
        $this->imagesBatchLoader = $imagesBatchLoader;
    }

    /**
     * @param object $entity
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByEntity(object $entity, ?string $type, ?array $sizes): Promise
    {
        return $this->resolveByEntityId($entity->getId(), static::$entityName, $type, $sizes);
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
                throw new UserError(sprintf('Image type "%s" not found for %s', $type, $entityName));
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
            throw new UserError(sprintf('Image size "%s" not found for %s', $size, $entityName));
        } catch (ImageTypeNotFoundException $e) {
            throw new UserError(sprintf('Image type "%s" not found for %s', $type, $entityName));
        }
    }
}
