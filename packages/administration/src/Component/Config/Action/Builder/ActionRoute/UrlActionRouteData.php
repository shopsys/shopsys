<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute;

use Closure;

class UrlActionRouteData implements ActionRouteInterface
{
    /**
     * @param \Closure(?object $entity): string $url
     */
    public function __construct(
        private readonly Closure $url,
    ) {
    }

    /**
     * @param object|null $entity
     * @return string
     */
    public function getUrl(?object $entity = null): string
    {
        return call_user_func($this->url, $entity);
    }
}
