<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActionColumn
{
    const TYPE_DELETE = 'delete';
    const TYPE_EDIT = 'edit';

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var RouteCsrfProtector
     */
    private $routeCsrfProtector;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $bindingRouteParams;

    /**
     * @var array
     */
    private $additionalRouteParams;

    /**
     * @var string|null
     */
    private $classAttribute;

    /**
     * @var string|null
     */
    private $confirmMessage;

    /**
     * @var bool
     */
    private $isAjaxConfirm;

    public function __construct(
        RouterInterface $router,
        RouteCsrfProtector $routeCsrfProtector,
        string $type,
        string $title,
        string $route,
        array $bindingRouteParams,
        array $additionalRouteParams
    ) {
        $this->router = $router;
        $this->routeCsrfProtector = $routeCsrfProtector;
        $this->type = $type;
        $this->title = $title;
        $this->route = $route;
        $this->bindingRouteParams = $bindingRouteParams;
        $this->additionalRouteParams = $additionalRouteParams;
        $this->isAjaxConfirm = false;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getClassAttribute(): ?string
    {
        return $this->classAttribute;
    }

    public function getConfirmMessage(): ?string
    {
        return $this->confirmMessage;
    }

    public function setClassAttribute(string $classAttribute): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        $this->classAttribute = $classAttribute;

        return $this;
    }

    public function setConfirmMessage(string $confirmMessage): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    public function setAjaxConfirm(): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        $this->isAjaxConfirm = true;

        return $this;
    }

    public function isAjaxConfirm(): bool
    {
        return $this->isAjaxConfirm;
    }

    public function getTargetUrl(array $row): string
    {
        $parameters = $this->additionalRouteParams;

        foreach ($this->bindingRouteParams as $key => $sourceColumnName) {
            $parameters[$key] = Grid::getValueFromRowBySourceColumnName($row, $sourceColumnName);
        }

        if ($this->type === self::TYPE_DELETE) {
            $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] = $this->routeCsrfProtector->getCsrfTokenByRoute($this->route);
        }

        return $this->router->generate($this->route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
