<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use Shopsys\AdministrationBundle\Component\Config\PageType;

class ActionData
{
    public string $linkUrl = 'javascript:void(0)';

    /**
     * @param string $name
     * @param string $label
     * @param string|null $icon
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionType $actionType
     * @param string $cssClass
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\ActionRouteType $routeType
     * @param string|null $route
     * @param string|null $crudController
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType|null $pageType
     * @param mixed|null $pageId
     * @param mixed|null $url
     * @param mixed $routeParameters
     * @param mixed|null $displayIf
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly ?string $icon,
        public readonly ActionType $actionType,
        public readonly string $cssClass,
        public readonly ActionRouteType $routeType,
        public readonly ?string $route,
        public readonly ?string $crudController,
        public readonly ?PageType $pageType,
        public $pageId = null,
        public $url = null,
        public $routeParameters = [],
        public $displayIf = null,
    ) {
    }

    /**
     * @param string $url
     */
    public function setLinkUrl(string $url): void
    {
        $this->linkUrl = $url;
    }
}
