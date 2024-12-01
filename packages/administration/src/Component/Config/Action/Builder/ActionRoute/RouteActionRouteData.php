<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute;

use Closure;

class RouteActionRouteData implements ActionRouteInterface
{
    /**
     * @param string $routeName
     * @param array|\Closure(?object $entity): array $routeParameters
     */
    public function __construct(
        private readonly string $routeName,
        private readonly array|Closure $routeParameters = [],
    ) {
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    /**
     * @param object|null $entity
     * @return array
     */
    public function getRouteParameters(?object $entity = null): array
    {
        if (is_array($this->routeParameters)) {
            return $this->routeParameters;
        }

        return call_user_func($this->routeParameters, $entity);
    }
}
