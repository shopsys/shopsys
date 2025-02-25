<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action\RouteData;

use Closure;

class UrlActionRouteData implements ActionRouteInterface
{
    /**
     * @param \Closure(mixed): string $url
     */
    public function __construct(
        private readonly Closure $url,
    ) {
    }

    /**
     * @param mixed|null $data
     * @return string
     */
    public function getUrl(mixed $data = null): string
    {
        return call_user_func($this->url, $data);
    }
}
