<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile\Config;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class UploadedFileConfigLoader
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig[]
     */
    private $uploadedFileEntityConfigsByClass;

    /**
     * @var string[]
     */
    private $entityNamesByEntityNames;

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
    public function loadFromYaml($filename)
    {
        $yamlParser = new Parser();

        if (!$this->filesystem->exists($filename)) {
            throw new \Symfony\Component\Filesystem\Exception\FileNotFoundException(
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
    private function loadFileEntityConfigsFromArray($outputConfig)
    {
        $this->uploadedFileEntityConfigsByClass = [];
        $this->entityNamesByEntityNames = [];

        foreach ($outputConfig as $entityConfig) {
            try {
                $uploadedFileEntityConfig = $this->processEntityConfig($entityConfig);
                $this->entityNamesByEntityNames[$uploadedFileEntityConfig->getEntityName()] = $uploadedFileEntityConfig->getEntityName();
                $this->uploadedFileEntityConfigsByClass[$uploadedFileEntityConfig->getEntityClass()] = $uploadedFileEntityConfig;
            } catch (\Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileConfigException $e) {
                throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\UploadedFileConfigurationParseException(
                    $entityConfig[UploadedFileConfigDefinition::CONFIG_CLASS],
                    $e
                );
            }
        }
    }

    /**
     * @param array $entityConfig
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileEntityConfig
     */
    private function processEntityConfig($entityConfig)
    {
        $entityClass = $entityConfig[UploadedFileConfigDefinition::CONFIG_CLASS];
        $entityName = $entityConfig[UploadedFileConfigDefinition::CONFIG_ENTITY_NAME];

        if (array_key_exists($entityClass, $this->uploadedFileEntityConfigsByClass)
            || array_key_exists($entityName, $this->entityNamesByEntityNames)
        ) {
            throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Config\Exception\DuplicateEntityNameException($entityName);
        }

        return new UploadedFileEntityConfig($entityName, $entityClass);
    }
}
