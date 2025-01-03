<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;

class ActionData
{
    public string $url = 'javascript:void(0)';

    /**
     * @param string $name
     * @param string $label
     * @param string|null $icon
     * @param string $cssClass
     * @param bool $openInNewTab
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface|null $actionRoute
     * @param null|Closure(?object $entity): bool $displayIf
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly ?string $icon,
        public readonly string $cssClass,
        public readonly bool $openInNewTab,
        public readonly ?ActionRouteInterface $actionRoute = null,
        public readonly ?Closure $displayIf = null,
    ) {
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
