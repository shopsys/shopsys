<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

interface AccessControlDataProviderInterface
{
    /**
     * @return \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData[]
     */
    public function getAll(): array;

    /**
     * @param string $routeName
     * @return \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData|null
     */
    public function findRouteByName(string $routeName): ?RouteAccessControlData;

    public function clearCache(): void;
}
