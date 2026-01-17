<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateEntityNameExceptionInvalid;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\DuplicateTypeNameExceptionInvalid;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\EntityParseException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\InvalidImageConfigException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class ImageConfigLoader
{
    /**
     * @var array<class-string, \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig>
     */
    protected array $foundEntityConfigs;

    /**
     * @var string[]
     */
    protected array $foundEntityNames;

    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function loadFromYaml(string $filename): ImageConfig
    {
        $yamlParser = new Parser();

        if (!$this->filesystem->exists($filename)) {
            throw new FileNotFoundException(
                'File ' . $filename . ' does not exist',
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
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig[]
     */
    public function loadFromArray(array $outputConfig): array
    {
        $this->foundEntityConfigs = [];
        $this->foundEntityNames = [];

        foreach ($outputConfig as $entityConfig) {
            try {
                $this->processEntityConfig($entityConfig);
            } catch (InvalidImageConfigException $e) {
                throw new EntityParseException(
                    $entityConfig[ImageConfigDefinition::CONFIG_CLASS],
                    $e,
                );
            }
        }

        return $this->foundEntityConfigs;
    }

    protected function processEntityConfig(array $entityConfig): void
    {
        $entityClass = $entityConfig[ImageConfigDefinition::CONFIG_CLASS];
        $entityName = $entityConfig[ImageConfigDefinition::CONFIG_ENTITY_NAME];

        if (array_key_exists($entityClass, $this->foundEntityConfigs)
            || array_key_exists($entityName, $this->foundEntityNames)
        ) {
            throw new DuplicateEntityNameExceptionInvalid($entityName);
        }

        $types = $this->prepareTypes($entityConfig[ImageConfigDefinition::CONFIG_TYPES]);
        $multipleByType = $this->getMultipleByType($entityConfig);

        $imageEntityConfig = new ImageEntityConfig($entityName, $entityClass, $types, $multipleByType);
        $this->foundEntityNames[$entityName] = $entityName;
        $this->foundEntityConfigs[$entityClass] = $imageEntityConfig;
    }

    /**
     * @return string[]
     */
    protected function prepareTypes(array $typesConfig): array
    {
        $result = [];

        foreach ($typesConfig as $typeConfig) {
            $typeName = $typeConfig[ImageConfigDefinition::CONFIG_TYPE_NAME];

            if (array_key_exists($typeName, $result)) {
                throw new DuplicateTypeNameExceptionInvalid($typeName);
            }

            $result[$typeName] = $typeName;
        }

        return $result;
    }

    /**
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
