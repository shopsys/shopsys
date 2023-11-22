<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActionColumn
{
    public const TYPE_DELETE = 'delete';
    public const TYPE_EDIT = 'edit';

    protected string $type;

    protected string $title;

    protected string $route;

    protected ?string $classAttribute = null;

    protected ?string $confirmMessage = null;

    protected bool $isAjaxConfirm;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param string $type
     * @param string $title
     * @param string $route
     * @param mixed[] $bindingRouteParams
     * @param mixed[] $additionalRouteParams
     */
    public function __construct(
        protected readonly RouterInterface $router,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        string $type,
        string $title,
        string $route,
        protected readonly array $bindingRouteParams,
        protected readonly array $additionalRouteParams,
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->route = $route;
        $this->isAjaxConfirm = false;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getClassAttribute(): ?string
    {
        return $this->classAttribute;
    }

    /**
     * @return string|null
     */
    public function getConfirmMessage(): ?string
    {
        return $this->confirmMessage;
    }

    /**
     * @param string $classAttribute
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function setClassAttribute(?string $classAttribute): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        $this->classAttribute = $classAttribute;

        return $this;
    }

    /**
     * @param string $confirmMessage
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function setConfirmMessage(?string $confirmMessage): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function setAjaxConfirm(): \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
    {
        $this->isAjaxConfirm = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAjaxConfirm(): bool
    {
        return $this->isAjaxConfirm;
    }

    /**
     * @param mixed[] $row
     * @return string
     */
    public function getTargetUrl(array $row): string
    {
        $parameters = $this->additionalRouteParams;

        foreach ($this->bindingRouteParams as $key => $sourceColumnName) {
            $parameters[$key] = Grid::getValueFromRowBySourceColumnName($row, $sourceColumnName);
        }

        if ($this->type === self::TYPE_DELETE) {
            $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] = $this->routeCsrfProtector->getCsrfTokenByRoute(
                $this->route,
            );
        }

        return $this->router->generate($this->route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
