<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ActionColumn
{
    public const string TYPE_DELETE = 'delete';
    public const string TYPE_EDIT = 'edit';
    public const string TYPE_RESET_PASSWORD = 'resetPassword';

    protected ?string $classAttribute = null;

    protected ?string $confirmMessage = null;

    protected bool $isAjaxConfirm = false;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector $routeCsrfProtector
     * @param string $type
     * @param string $title
     * @param string $route
     * @param array $bindingRouteParams
     * @param array $additionalRouteParams
     * @param \Shopsys\FrameworkBundle\Component\Grid\Grid $grid
     */
    public function __construct(
        protected readonly RouterInterface $router,
        protected readonly RouteCsrfProtector $routeCsrfProtector,
        protected readonly string $type,
        protected readonly string $title,
        protected readonly string $route,
        protected readonly array $bindingRouteParams,
        protected readonly array $additionalRouteParams,
        protected readonly Grid $grid,
    ) {
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
    public function setClassAttribute(string $classAttribute): self
    {
        $this->classAttribute = $classAttribute;

        return $this;
    }

    /**
     * @param string $confirmMessage
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function setConfirmMessage(string $confirmMessage): self
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\ActionColumn
     */
    public function setAjaxConfirm(): self
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
     * @param array $row
     * @return string
     */
    public function getTargetUrl(array $row): string
    {
        $parameters = $this->additionalRouteParams;

        foreach ($this->bindingRouteParams as $key => $sourceColumnName) {
            $parameters[$key] = $this->grid->getValueFromRowBySourceColumnName($row, $sourceColumnName);
        }

        if ($this->type === self::TYPE_DELETE || $this->type === self::TYPE_RESET_PASSWORD) {
            $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] = $this->routeCsrfProtector->getCsrfTokenByRoute(
                $this->route,
            );
        }

        return $this->router->generate($this->route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
