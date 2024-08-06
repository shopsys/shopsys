<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\CustomerUploadedFileConfigException;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\CustomerUploadedFileConfigurationParseException;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\DuplicateEntityNameExceptionUploaded;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\DuplicateTypeNameExceptionUploaded;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\Exception\NotSupportedTypeNameExceptionUploaded;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class CustomerUploadedFileConfigLoader
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileEntityConfig[]
     */
    protected array $customerUploadedFileEntityConfigsByClass;

    /**
     * @var string[]
     */
    protected array $entityNamesByEntityNames;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(protected readonly Filesystem $filesystem)
    {
    }

    /**
     * @param string $filename
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileConfig
     */
    public function loadFromYaml(string $filename): CustomerUploadedFileConfig
    {
        $yamlParser = new Parser();

        if (!$this->filesystem->exists($filename)) {
            throw new FileNotFoundException(
                'File ' . $filename . ' does not exist',
            );
        }

        $customerUploadedFileConfigDefinition = new CustomerUploadedFileConfigDefinition();
        $processor = new Processor();

        $inputConfig = $yamlParser->parse(file_get_contents($filename));
        $outputConfig = $processor->processConfiguration($customerUploadedFileConfigDefinition, [$inputConfig]);
        $this->loadFileEntityConfigsFromArray($outputConfig);

        return new CustomerUploadedFileConfig($this->customerUploadedFileEntityConfigsByClass);
    }

    /**
     * @param array $outputConfig
     */
    protected function loadFileEntityConfigsFromArray(array $outputConfig): void
    {
        $this->customerUploadedFileEntityConfigsByClass = [];
        $this->entityNamesByEntityNames = [];

        foreach ($outputConfig as $entityConfig) {
            try {
                $customerUploadedFileEntityConfig = $this->processEntityConfig($entityConfig);
                $this->entityNamesByEntityNames[$customerUploadedFileEntityConfig->getEntityName()] = $customerUploadedFileEntityConfig->getEntityName();
                $this->customerUploadedFileEntityConfigsByClass[$customerUploadedFileEntityConfig->getEntityClass()] = $customerUploadedFileEntityConfig;
            } catch (CustomerUploadedFileConfigException $e) {
                throw new CustomerUploadedFileConfigurationParseException(
                    $entityConfig[CustomerUploadedFileConfigDefinition::CONFIG_CLASS],
                    $e,
                );
            }
        }
    }

    /**
     * @param array $typesConfig
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileTypeConfig[]
     */
    protected function prepareTypes(array $typesConfig): array
    {
        $result = [];

        foreach ($typesConfig as $typeConfig) {
            $typeName = $typeConfig[CustomerUploadedFileConfigDefinition::CONFIG_TYPE_NAME];
            $typeMultiple = $typeConfig[CustomerUploadedFileConfigDefinition::CONFIG_TYPE_MULTIPLE];

            if ($typeName === null) {
                throw new NotSupportedTypeNameExceptionUploaded($typeName);
            }

            if (array_key_exists($typeName, $result)) {
                throw new DuplicateTypeNameExceptionUploaded($typeName);
            }

            $result[$typeName] = new CustomerUploadedFileTypeConfig($typeName, $typeMultiple);
        }

        return $result;
    }

    /**
     * @param array $entityConfig
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Config\CustomerUploadedFileEntityConfig
     */
    protected function processEntityConfig(array $entityConfig): CustomerUploadedFileEntityConfig
    {
        $entityClass = $entityConfig[CustomerUploadedFileConfigDefinition::CONFIG_CLASS];
        $entityName = $entityConfig[CustomerUploadedFileConfigDefinition::CONFIG_ENTITY_NAME];

        if (array_key_exists($entityClass, $this->customerUploadedFileEntityConfigsByClass)
            || array_key_exists($entityName, $this->entityNamesByEntityNames)
        ) {
            throw new DuplicateEntityNameExceptionUploaded($entityName);
        }

        $typesByName = $this->prepareTypes($entityConfig[CustomerUploadedFileConfigDefinition::CONFIG_TYPES]);

        return new CustomerUploadedFileEntityConfig($entityName, $entityClass, $typesByName);
    }
}
