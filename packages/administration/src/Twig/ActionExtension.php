<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use InvalidArgumentException;
use Override;
use Shopsys\AdministrationBundle\Component\Action\RouteData\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Action\RouteData\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlDataProviderInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActionExtension extends AbstractExtension
{
    public function __construct(
        protected readonly AdministrationRouter $router,
        protected readonly RouteAccessCheckerInterface $routeAccessChecker,
        protected readonly RouteCsrfProtector $csrfProtector,
        protected readonly AccessControlDataProviderInterface $accessControlDataProvider,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'action_url',
                $this->generateActionUrl(...),
            ),
            new TwigFunction(
                'action_has_access',
                $this->checkActionAccess(...),
            ),
        ];
    }

    protected function generateActionUrl(?ActionRouteInterface $actionRoute, mixed $data): string
    {
        if ($actionRoute === null) {
            return 'javascript:void(0)';
        }

        if ($actionRoute instanceof UrlActionRouteData) {
            return $actionRoute->getUrl($data);
        }

        if ($actionRoute instanceof CrudActionRouteData) {
            $parameters = $actionRoute->getId($data) !== null ? ['id' => $actionRoute->getId($data)] : [];

            if ($this->csrfProtector->isActionProtected($actionRoute->getCrudController(), $actionRoute->getActionType()->value . 'Action')) {
                $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] = $this->csrfProtector->getCsrfTokenByRoute($actionRoute->getRouteName());
            }

            return $this->router->generate($actionRoute->getRouteName(), $parameters);
        }

        if ($actionRoute instanceof RouteActionRouteData) {
            $parameters = $actionRoute->getRouteParameters($data);
            $routeData = $this->accessControlDataProvider->findRouteByName($actionRoute->getRouteName());

            if (
                $routeData !== null
                && class_exists($routeData->controllerClass)
                && method_exists($routeData->controllerClass, $routeData->controllerMethod)
                && $this->csrfProtector->isActionProtected($routeData->controllerClass, $routeData->controllerMethod)
            ) {
                $parameters[RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER] ??= $this->csrfProtector->getCsrfTokenByRoute($actionRoute->getRouteName());
            }

            return $this->router->generate($actionRoute->getRouteName(), $parameters);
        }

        throw new InvalidArgumentException('Action has invalid route type');
    }

    protected function checkActionAccess(?ActionRouteInterface $actionRoute): bool
    {
        if ($actionRoute === null) {
            return true;
        }

        if ($actionRoute instanceof UrlActionRouteData) {
            return true;
        }

        if ($actionRoute instanceof CrudActionRouteData || $actionRoute instanceof RouteActionRouteData) {
            return $this->routeAccessChecker->hasAccess($actionRoute->getRouteName(), HttpMethod::GET);
        }

        return false;
    }
}
