<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Override;
use Shopsys\AdministrationBundle\Component\Configuration\AccessControlConfiguration;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class RouteAccessChecker implements RouteAccessCheckerInterface
{
    public function __construct(
        private AccessControlDataProviderInterface $accessControlDataProvider,
        private AccessControlConfiguration $accessControlConfiguration,
        private Security $security,
    ) {
    }

    /**
     * Check if current user has access to a route with specific HTTP method
     */
    #[Override]
    public function hasAccess(string $routeName, HttpMethod|string $httpMethod): bool
    {
        if (in_array($routeName, $this->accessControlConfiguration->getExcludedRouteNames(), true)) {
            return true;
        }

        $routeData = $this->accessControlDataProvider->findRouteByName($routeName);

        if ($routeData === null) {
            return false;
        }

        $method = HttpMethod::getValidHttpMethod($httpMethod);

        return $routeData->hasAccess($method, fn (string $role) => $this->security->isGranted($role));
    }
}
