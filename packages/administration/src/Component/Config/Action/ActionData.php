<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use Shopsys\AdministrationBundle\Component\Config\PageType;

class ActionData
{
    /**
     * @var callable|null
     */
    public $displayIf = null;

    /**
     * @var callable|null
     */
    public $pageId = null;

    /**
     * @var callable|string|null
     */
    public $url = null;

    /**
     * @var array|callable
     */
    public $routeParameters = [];

    public string $linkUrl = 'javascript:void(0)';

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
        $pageId,
        $url,
        $routeParameters,
        $displayIf,
    ) {
        $this->pageId = $pageId;
        $this->url = $url;
        $this->routeParameters = $routeParameters;
        $this->displayIf = $displayIf;
    }

    /**
     * @param string $url
     * @return void
     */
    public function setLinkUrl(string $url): void
    {
        $this->linkUrl = $url;
    }
}