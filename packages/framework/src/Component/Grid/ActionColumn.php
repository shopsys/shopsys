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

    public function setClassAttribute(string $classAttribute): static
    {
        $this->classAttribute = $classAttribute;

        return $this;
    }

    public function setConfirmMessage(string $confirmMessage): static
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    public function setAjaxConfirm(): static
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
