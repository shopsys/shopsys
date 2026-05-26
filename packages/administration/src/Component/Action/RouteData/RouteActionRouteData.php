<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action\RouteData;

use Closure;

final class RouteActionRouteData implements ActionRouteInterface
{
    /**
     * @param array|\Closure(mixed): array $routeParameters
     */
    public function __construct(
        private readonly string $routeName,
        private readonly array|Closure $routeParameters = [],
    ) {
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function getRouteParameters(mixed $data = null): array
    {
        if (is_array($this->routeParameters)) {
            return $this->routeParameters;
        }

        return call_user_func($this->routeParameters, $data);
    }
}
