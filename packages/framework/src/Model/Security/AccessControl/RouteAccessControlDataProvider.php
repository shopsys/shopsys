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
        $accessControlCacheFile = $this->cacheDirectory . '/access_control_rules.json';

        if (file_exists($accessControlCacheFile)) {
            $accessControlRulesArray = json_decode(file_get_contents($accessControlCacheFile), true, 512, JSON_THROW_ON_ERROR);
            $routeAccessControlRules = [];

            foreach ($accessControlRulesArray as $accessControlRuleArray) {
                $routeAccessControlRules[] = RouteAccessControlData::fromArray($accessControlRuleArray);
            }
        } else {
            $accessControlRulesAttributeFinder = new AccessControlRulesAttributeFinder();

            $routeAccessControlRules = $accessControlRulesAttributeFinder->findAll($this->getControllerDirectories());

            file_put_contents($accessControlCacheFile, json_encode($routeAccessControlRules, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
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
