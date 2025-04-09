<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action\RouteData;

use Closure;

class UrlActionRouteData implements ActionRouteInterface
{
    /**
     * @param string|\Closure(mixed): string $url
     */
    public function __construct(
        private readonly Closure|string $url,
    ) {
    }

    /**
     * @param mixed|null $data
     * @return string
     */
    public function getUrl(mixed $data = null): string
    {
        if (is_string($this->url)) {
            return $this->url;
        }

        return call_user_func($this->url, $data);
    }
}
