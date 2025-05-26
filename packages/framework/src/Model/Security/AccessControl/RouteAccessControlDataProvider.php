<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;

class RouteAccessControlDataProvider
{
    /**
     * @param string $projectRootDirectory
     * @param string $cacheDirectory
     * @param string $frameworkRootDirectory
     * @param array $packagesRegistry
     * @param string $environment
     */
    public function __construct(
        protected readonly string $projectRootDirectory,
        protected readonly string $cacheDirectory,
        protected readonly string $frameworkRootDirectory,
        protected readonly array $packagesRegistry,
        protected readonly string $environment,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlData[]
     */
    public function findAll(): array
    {
        $accessControlCacheFile = $this->cacheDirectory . '/access_control_rules.php';
        $isDev = $this->environment === EnvironmentType::DEVELOPMENT;

        if (!$isDev && file_exists($accessControlCacheFile)) {
            $accessControlRulesArray = require $accessControlCacheFile;
            $routeAccessControlRules = [];

            foreach ($accessControlRulesArray as $accessControlRuleArray) {
                $routeAccessControlRules[] = $accessControlRuleArray;
            }
        } else {
            $accessControlRulesAttributeFinder = new AccessControlRulesAttributeFinder();

            $routeAccessControlRules = $accessControlRulesAttributeFinder->findAll($this->getControllerDirectories());

            if (!$isDev) {
                file_put_contents($accessControlCacheFile, sprintf('<?php return %s;', var_export($routeAccessControlRules, true)));
            }
        }

        return $routeAccessControlRules;
    }

    /**
     * @return string[]
     */
    protected function getControllerDirectories(): array
    {
        $controllerDirectories = [
            $this->projectRootDirectory . '/src/Controller/Admin',
        ];

        foreach ($this->packagesRegistry as $package) {
            $expectedControllerPath = $this->getResolvedPackagePath($package['path']) . '/src/Controller';

            if (is_dir($expectedControllerPath)) {
                $controllerDirectories[] = $expectedControllerPath;
            }
        }

        return $controllerDirectories;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getResolvedPackagePath(string $path): string
    {
        $path = str_replace(['%shopsys.framework.root_dir%', '//'], [$this->frameworkRootDirectory, '/'], $path);

        return rtrim($path, '/');
    }
}
