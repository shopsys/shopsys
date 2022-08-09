<?php

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateEntityNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateMediaException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateSizeNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateTypeNameException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\EntityParseException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageConfigException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\WidthAndHeightMissingException;
use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

/**
 * @phpstan-type AdditionalSizeArray array{
 *     media: string,
 *     height: int|null,
 *     width: int|null,
 * }
 * @phpstan-type SizeConfigArray array{
 *     name: string|null,
 *     additionalSizes: array<array{
 *         media: string,
 *         height: int|null,
 *         width: int|null,
 *     }>,
 *     width: int|null,
 *     height: int|null,
 *     crop: bool,
 *     occurrence: string|null,
 * }
 * @phpstan-type TypeConfigArray array{
 *     name: string,
 *     sizes: array<array{
 *          name: string|null,
 *          additionalSizes: array<array{
 *              media: string,
 *             height: int|null,
 *              width: int|null,
 *          }>,
 *          width: int|null,
 *          height: int|null,
 *          crop: bool,
 *          occurrence: string|null,
 *     }>,
 *     multiple: bool,
 * }
 */
class ImageConfigLoader
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    protected $foundEntityConfigs;

    /**
     * @var string[]
     */
    protected $foundEntityNames;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(Filesystem $filesystem, EntityNameResolver $entityNameResolver)
    {
        $this->filesystem = $filesystem;
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param string $filename
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    public function loadFromYaml(string $filename): ImageConfig
    {
        $yamlParser = new Parser();

        if (!$this->filesystem->exists($filename)) {
            throw new FileNotFoundException(
                'File ' . $filename . ' does not exist'
            );
        }

        $imageConfigDefinition = new ImageConfigDefinition();
        $processor = new Processor();

        $inputConfig = $yamlParser->parse(file_get_contents($filename));
        $outputConfig = $processor->processConfiguration($imageConfigDefinition, [$inputConfig]);

        $preparedConfig = $this->loadFromArray($outputConfig);

        return new ImageConfig($preparedConfig, $this->entityNameResolver);
    }

    /**
     * @param mixed[] $outputConfig
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    public function loadFromArray(array $outputConfig): array
    {
        $this->foundEntityConfigs = [];
        $this->foundEntityNames = [];

        foreach ($outputConfig as $entityConfig) {
            try {
                $this->processEntityConfig($entityConfig);
            } catch (ImageConfigException $e) {
                throw new EntityParseException(
                    $entityConfig[ImageConfigDefinition::CONFIG_CLASS],
                    $e
                );
            }
        }

        return $this->foundEntityConfigs;
    }

    /**
     * @param array{
     *     class: class-string,
     *     name: string,
     *     types: TypeConfigArray[],
     *     sizes: SizeConfigArray[],
     *     multiple: bool,
     * } $entityConfig
     */
    protected function processEntityConfig(array $entityConfig): void
    {
        $entityClass = $entityConfig[ImageConfigDefinition::CONFIG_CLASS];
        $entityName = $entityConfig[ImageConfigDefinition::CONFIG_ENTITY_NAME];

        if (array_key_exists($entityClass, $this->foundEntityConfigs)
            || array_key_exists($entityName, $this->foundEntityNames)
        ) {
            throw new DuplicateEntityNameException($entityName);
        }

        $types = $this->prepareTypes($entityConfig[ImageConfigDefinition::CONFIG_TYPES]);
        $sizes = $this->prepareSizes($entityConfig[ImageConfigDefinition::CONFIG_SIZES]);
        $multipleByType = $this->getMultipleByType($entityConfig);

        $imageEntityConfig = new ImageEntityConfig($entityName, $entityClass, $types, $sizes, $multipleByType);
        $this->foundEntityNames[$entityName] = $entityName;
        $this->foundEntityConfigs[$entityClass] = $imageEntityConfig;
    }

    /**
     * @param SizeConfigArray[] $sizesConfig
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    protected function prepareSizes(array $sizesConfig): array
    {
        $result = [];
        foreach ($sizesConfig as $sizeConfig) {
            $sizeName = $sizeConfig[ImageConfigDefinition::CONFIG_SIZE_NAME];
            $key = Utils::ifNull($sizeName, ImageEntityConfig::WITHOUT_NAME_KEY);
            $additionalSizes = $this->prepareAdditionalSizes(
                $sizeName ?: '~',
                $sizeConfig[ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES]
            );
            if (array_key_exists($key, $result)) {
                throw new DuplicateSizeNameException($sizeName);
            }

            $result[$key] = new ImageSizeConfig(
                $sizeName,
                $sizeConfig[ImageConfigDefinition::CONFIG_SIZE_WIDTH],
                $sizeConfig[ImageConfigDefinition::CONFIG_SIZE_HEIGHT],
                $sizeConfig[ImageConfigDefinition::CONFIG_SIZE_CROP],
                $sizeConfig[ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE],
                $additionalSizes
            );
        }
        if (!array_key_exists(ImageConfig::ORIGINAL_SIZE_NAME, $result)) {
            $result[ImageConfig::ORIGINAL_SIZE_NAME] = new ImageSizeConfig(
                ImageConfig::ORIGINAL_SIZE_NAME,
                null,
                null,
                false,
                null,
                []
            );
        }

        return $result;
    }

    /**
     * @param string $sizeName
     * @param AdditionalSizeArray[] $additionalSizesConfig
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig[]
     */
    protected function prepareAdditionalSizes(string $sizeName, array $additionalSizesConfig): array
    {
        $usedMedia = [];
        $result = [];
        foreach ($additionalSizesConfig as $index => $additionalSizeConfig) {
            $media = $additionalSizeConfig[ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZE_MEDIA];
            $height = $additionalSizeConfig[ImageConfigDefinition::CONFIG_SIZE_HEIGHT];
            $width = $additionalSizeConfig[ImageConfigDefinition::CONFIG_SIZE_WIDTH];
            if ($width === null && $height === null) {
                throw new WidthAndHeightMissingException(sprintf('%s.additionalSizes[%s]', $sizeName, $index));
            }
            if (in_array($media, $usedMedia, true)) {
                throw new DuplicateMediaException($media);
            }
            $usedMedia[] = $media;

            $result[] = new ImageAdditionalSizeConfig(
                $width,
                $height,
                $media
            );
        }
        return $result;
    }

    /**
     * @param TypeConfigArray[] $typesConfig
     * @return array<string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]>
     */
    protected function prepareTypes(array $typesConfig): array
    {
        $result = [];
        foreach ($typesConfig as $typeConfig) {
            $typeName = $typeConfig[ImageConfigDefinition::CONFIG_TYPE_NAME];
            if (array_key_exists($typeName, $result)) {
                throw new DuplicateTypeNameException($typeName);
            }

            $result[$typeName] = $this->prepareSizes($typeConfig[ImageConfigDefinition::CONFIG_SIZES]);
        }

        return $result;
    }

    /**
     * @param array{
     *     multiple: bool,
     *     types: TypeConfigArray[],
     * } $entityConfig
     * @return array<string, bool>
     */
    protected function getMultipleByType(array $entityConfig): array
    {
        $multipleByType = [];
        $multipleByType[ImageEntityConfig::WITHOUT_NAME_KEY] = $entityConfig[ImageConfigDefinition::CONFIG_MULTIPLE];
        foreach ($entityConfig[ImageConfigDefinition::CONFIG_TYPES] as $typeConfig) {
            $type = $typeConfig[ImageConfigDefinition::CONFIG_TYPE_NAME];
            $multiple = $typeConfig[ImageConfigDefinition::CONFIG_MULTIPLE];
            $multipleByType[$type] = $multiple;
        }

        return $multipleByType;
    }
}
