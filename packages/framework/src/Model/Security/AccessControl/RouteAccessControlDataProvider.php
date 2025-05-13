<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security\AccessControl;

class RouteAccessControlDataProvider
{
    /**
     * @param string $projectRootDirectory
     * @param string $cacheDirectory
     */
    public function __construct(
        protected readonly string $projectRootDirectory,
        protected readonly string $cacheDirectory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Security\AccessControl\RouteAccessControlData[]
     */
    public function findAll(): array
    {
        $accessControlCacheFile = $this->cacheDirectory . '/access_control_rules.php';

        if (file_exists($accessControlCacheFile)) {
            $accessControlRulesArray = require $accessControlCacheFile;
            $routeAccessControlRules = [];

            foreach ($accessControlRulesArray as $accessControlRuleArray) {
                $routeAccessControlRules[] = $accessControlRuleArray;
            }
        } else {
            $accessControlRulesAttributeFinder = new AccessControlRulesAttributeFinder();

            $routeAccessControlRules = $accessControlRulesAttributeFinder->findAll($this->getControllerDirectories());

            file_put_contents($accessControlCacheFile, sprintf('<?php return %s;', var_export($routeAccessControlRules, true)));
        }

        return $routeAccessControlRules;
    }

    /**
     * @return string[]
     */
    protected function getControllerDirectories(): array
    {
        if (file_exists($this->projectRootDirectory . '/../../parameters_monorepo.yaml')) {
            return [
                $this->projectRootDirectory . '/src/Controller/Admin',
                $this->projectRootDirectory . '/../../packages/framework/src/Controller/Admin',
                $this->projectRootDirectory . '/../../packages/frontend-api/src/Controller',
            ];
        }

        return [
            $this->projectRootDirectory . '/src/Controller/Admin',
            $this->projectRootDirectory . '/vendor/shopsys/framework/src/Controller/Admin',
            $this->projectRootDirectory . '/vendor/shopsys/frontend-api/src/Controller',
        ];
    }
}
