<?php

namespace Shopsys\FrameworkBundle\Component\Environment;

class EnvironmentFileSetting
{
    const FILE_NAMES_BY_ENVIRONMENT = [
        EnvironmentType::DEVELOPMENT => 'DEVELOPMENT',
        EnvironmentType::PRODUCTION => 'PRODUCTION',
        EnvironmentType::TEST => 'TEST',
    ];

    const ENVIRONMENTS_CONSOLE = [EnvironmentType::DEVELOPMENT, EnvironmentType::PRODUCTION];
    const ENVIRONMENTS_DEFAULT = [EnvironmentType::TEST, EnvironmentType::DEVELOPMENT, EnvironmentType::PRODUCTION];

    /**
     * @var string
     */
    private $environmentFileDirectory;

    public function __construct(string $environmentFileDirectory)
    {
        $this->environmentFileDirectory = $environmentFileDirectory;
    }

    public function getEnvironment(bool $console): string
    {
        $environments = $console ? self::ENVIRONMENTS_CONSOLE : self::ENVIRONMENTS_DEFAULT;
        foreach ($environments as $environment) {
            if (is_file($this->getEnvironmentFilePath($environment))) {
                return $environment;
            }
        }

        return EnvironmentType::PRODUCTION;
    }

    public function isAnyEnvironmentSet(): bool
    {
        foreach (EnvironmentType::ALL as $environment) {
            if (is_file($this->getEnvironmentFilePath($environment))) {
                return true;
            }
        }

        return false;
    }

    public function createFileForEnvironment(string $environment): void
    {
        touch($this->getEnvironmentFilePath($environment));
    }

    private function getEnvironmentFilePath(string $environment): string
    {
        return $this->environmentFileDirectory . '/' . self::FILE_NAMES_BY_ENVIRONMENT[$environment];
    }
}
