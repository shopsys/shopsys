<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\DuplicateEntityNameException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\DuplicateTypeNameException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\NotSupportedTypeNameException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileConfigException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileConfigurationParseException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class UploadedFileConfigLoader
{
    protected Filesystem $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    protected array $uploadedFileEntityConfigsByClass;

    /**
     * @var string[]
     */
    protected array $entityNamesByEntityNames;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $filename
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig
     */
    public function loadFromYaml(string $filename): UploadedFileConfig
    {
        $yamlParser = new Parser();

        if (!$this->filesystem->exists($filename)) {
            throw new FileNotFoundException(
                'File ' . $filename . ' does not exist'
            );
        }

        $uploadedFileConfigDefinition = new UploadedFileConfigDefinition();
        $processor = new Processor();

        $inputConfig = $yamlParser->parse(file_get_contents($filename));
        $outputConfig = $processor->processConfiguration($uploadedFileConfigDefinition, [$inputConfig]);
        $this->loadFileEntityConfigsFromArray($outputConfig);

        return new UploadedFileConfig($this->uploadedFileEntityConfigsByClass);
    }

    /**
     * @param array $outputConfig
     */
    protected function loadFileEntityConfigsFromArray(array $outputConfig): void
    {
        $this->uploadedFileEntityConfigsByClass = [];
        $this->entityNamesByEntityNames = [];

        foreach ($outputConfig as $entityConfig) {
            try {
                $uploadedFileEntityConfig = $this->processEntityConfig($entityConfig);
                $this->entityNamesByEntityNames[$uploadedFileEntityConfig->getEntityName()] = $uploadedFileEntityConfig->getEntityName();
                $this->uploadedFileEntityConfigsByClass[$uploadedFileEntityConfig->getEntityClass()] = $uploadedFileEntityConfig;
            } catch (UploadedFileConfigException $e) {
                throw new UploadedFileConfigurationParseException(
                    $entityConfig[UploadedFileConfigDefinition::CONFIG_CLASS],
                    $e
                );
            }
        }
    }

    /**
     * @param array $typesConfig
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig[]
     */
    protected function prepareTypes(array $typesConfig): array
    {
        $result = [];

        foreach ($typesConfig as $typeConfig) {
            $typeName = $typeConfig[UploadedFileConfigDefinition::CONFIG_TYPE_NAME];
            $typeMultiple = $typeConfig[UploadedFileConfigDefinition::CONFIG_TYPE_MULTIPLE];

            if ($typeName === null) {
                throw new NotSupportedTypeNameException($typeName);
            }

            if (array_key_exists($typeName, $result)) {
                throw new DuplicateTypeNameException($typeName);
            }

            $result[$typeName] = new UploadedFileTypeConfig($typeName, $typeMultiple);
        }

        return $result;
    }

    /**
     * @param array $entityConfig
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig
     */
    protected function processEntityConfig(array $entityConfig): UploadedFileEntityConfig
    {
        $entityClass = $entityConfig[UploadedFileConfigDefinition::CONFIG_CLASS];
        $entityName = $entityConfig[UploadedFileConfigDefinition::CONFIG_ENTITY_NAME];

        if (array_key_exists($entityClass, $this->uploadedFileEntityConfigsByClass)
            || array_key_exists($entityName, $this->entityNamesByEntityNames)
        ) {
            throw new DuplicateEntityNameException($entityName);
        }

        $typesByName = $this->prepareTypes($entityConfig[UploadedFileConfigDefinition::CONFIG_TYPES]);

        return new UploadedFileEntityConfig($entityName, $entityClass, $typesByName);
    }
}
