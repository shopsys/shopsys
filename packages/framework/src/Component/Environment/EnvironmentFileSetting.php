<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Environment;

class EnvironmentFileSetting
{
    protected const FILE_NAMES_BY_ENVIRONMENT = [
        EnvironmentType::DEVELOPMENT => 'DEVELOPMENT',
        EnvironmentType::PRODUCTION => 'PRODUCTION',
        EnvironmentType::TEST => 'TEST',
    ];

    protected const ENVIRONMENTS_DEFAULT = [
        EnvironmentType::TEST,
        EnvironmentType::DEVELOPMENT,
        EnvironmentType::PRODUCTION,
    ];

    public function __construct(protected readonly string $environmentFileDirectory)
    {
    }

    public function getEnvironment(): string
    {
        $environments = static::ENVIRONMENTS_DEFAULT;

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

    public function removeFilesForAllEnvironments(): void
    {
        foreach (EnvironmentType::ALL as $environment) {
            $environmentFilePath = $this->getEnvironmentFilePath($environment);

            if (is_file($environmentFilePath)) {
                unlink($environmentFilePath);
            }
        }
    }

    public function getEnvironmentFilePath(string $environment): string
    {
        return $this->environmentFileDirectory . '/' . static::FILE_NAMES_BY_ENVIRONMENT[$environment];
    }
}
