<?php

namespace Shopsys\FrameworkBundle\Component\Environment;

use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;

class EnvironmentFileSetting
{
    protected const FILE_NAMES_BY_ENVIRONMENT = [
        EnvironmentType::DEVELOPMENT => 'DEVELOPMENT',
        EnvironmentType::PRODUCTION => 'PRODUCTION',
        EnvironmentType::TEST => 'TEST',
        EnvironmentType::ACCEPTANCE => 'ACCEPTANCE',
    ];

    /**
     * @deprecated constant ENVIRONMENTS_CONSOLE is deprecated and will be removed in next major version
     */
    protected const ENVIRONMENTS_CONSOLE = [
        EnvironmentType::ACCEPTANCE,
        EnvironmentType::TEST,
        EnvironmentType::DEVELOPMENT,
        EnvironmentType::PRODUCTION,
    ];
    protected const ENVIRONMENTS_DEFAULT = [
        EnvironmentType::ACCEPTANCE,
        EnvironmentType::TEST,
        EnvironmentType::DEVELOPMENT,
        EnvironmentType::PRODUCTION,
    ];

    /**
     * @var string
     */
    protected $environmentFileDirectory;

    /**
     * @param string $environmentFileDirectory
     */
    public function __construct(string $environmentFileDirectory)
    {
        $this->environmentFileDirectory = $environmentFileDirectory;
    }

    /**
     * @param bool|null $console
     * @return string
     */
    public function getEnvironment(?bool $console = null): string
    {
        if ($console !== null) {
            DeprecationHelper::trigger(
                'The $console parameter of %s() method is deprecated and will be removed in the next major.',
                __METHOD__
            );
        }

        $environments = static::ENVIRONMENTS_DEFAULT;
        foreach ($environments as $environment) {
            if (is_file($this->getEnvironmentFilePath($environment))) {
                return $environment;
            }
        }

        return EnvironmentType::PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isAnyEnvironmentSet(): bool
    {
        foreach (EnvironmentType::ALL as $environment) {
            if (is_file($this->getEnvironmentFilePath($environment))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $environment
     */
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

    /**
     * @param string $environment
     * @return string
     */
    public function getEnvironmentFilePath(string $environment): string
    {
        return $this->environmentFileDirectory . '/' . static::FILE_NAMES_BY_ENVIRONMENT[$environment];
    }
}
