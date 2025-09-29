<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use InvalidArgumentException;
use Override;
use Psr\Log\LoggerInterface;
use ReflectionException;
use ReflectionMethod;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Provides cached access control data for routes
 * Builds access control rules from route attributes
 * This class is used internally by the access control system
 *
 * @internal
 */
final class RouteAccessControlDataProvider implements AccessControlDataProviderInterface
{
    private const string CACHE_KEY = 'shopsys_access_control_rules';

    /**
     * @var array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>|null
     */
    private ?array $routeAccessControlDataIndexedByRouteName = null;

    /**
     * @param \Symfony\Contracts\Cache\CacheInterface $cache
     * @param string $environment
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $adminUrl
     * @param \Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor $attributeProcessor
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly string $environment,
        private readonly DomainRouterFactory $domainRouterFactory,
        private readonly LoggerInterface $logger,
        private readonly string $adminUrl,
        private readonly AttributeProcessor $attributeProcessor,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAll(): array
    {
        return $this->getRouteAccessControlDataIndexedByRouteName();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function findRouteByName(string $routeName): ?RouteAccessControlData
    {
        $indexedRoutes = $this->getRouteAccessControlDataIndexedByRouteName();

        return $indexedRoutes[$routeName] ?? null;
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>
     */
    private function getRouteAccessControlDataIndexedByRouteName(): array
    {
        if ($this->routeAccessControlDataIndexedByRouteName !== null) {
            return $this->routeAccessControlDataIndexedByRouteName;
        }

        if ($this->environment !== EnvironmentType::PRODUCTION) {
            $this->routeAccessControlDataIndexedByRouteName = $this->buildRoutes();
        } else {
            $this->routeAccessControlDataIndexedByRouteName = $this->cache->get(self::CACHE_KEY, function () {
                return $this->buildRoutes();
            });
        }

        return $this->routeAccessControlDataIndexedByRouteName;
    }

    #[Override]
    public function clearCache(): void
    {
        $this->cache->delete(self::CACHE_KEY);

        // Clear local cache as well
        $this->routeAccessControlDataIndexedByRouteName = null;
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData>
     */
    private function buildRoutes(): array
    {
        $router = $this->domainRouterFactory->getRouter(Domain::FIRST_DOMAIN_ID);
        $routes = $router->getRouteCollection();
        $adminRoutes = $this->filterAdminRoutes($routes);
        $indexedRoutes = [];

        foreach ($adminRoutes as $routeName => $route) {
            $controller = $route->getDefault('_controller');

            if (!$controller || !is_string($controller)) {
                $indexedRoutes[$routeName] = new RouteAccessControlData(
                    $routeName,
                    [],
                    'UnknownController',
                    'unknownMethod',
                );

                continue;
            }

            try {
                [$controllerClass, $method] = $this->parseController($controller);

                if (!class_exists($controllerClass)) {
                    $indexedRoutes[$routeName] = new RouteAccessControlData(
                        $routeName,
                        [],
                        $controllerClass,
                        $method,
                    );

                    continue;
                }

                $reflectionMethod = new ReflectionMethod($controllerClass, $method);
                $rules = $this->attributeProcessor->processMethod($reflectionMethod);

                $indexedRoutes[$routeName] = new RouteAccessControlData(
                    $routeName,
                    $rules,
                    $controllerClass,
                    $method,
                );
            } catch (ReflectionException | InvalidArgumentException $exception) {
                $this->logger->error(
                    'Skipping route with invalid controller or method',
                    [
                        'route_name' => $routeName,
                        'controller' => $controller,
                        'exception_message' => $exception->getMessage(),
                        'exception_type' => get_class($exception),
                    ],
                );

                $indexedRoutes[$routeName] = new RouteAccessControlData(
                    $routeName,
                    [],
                    explode('::', $controller)[0] ?? 'UnknownController',
                    'unknownMethod',
                );
            }
        }

        return $indexedRoutes;
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @return array<string, \Symfony\Component\Routing\Route>
     */
    private function filterAdminRoutes(RouteCollection $routes): array
    {
        $adminRoutes = [];
        $adminPathPrefix = sprintf('/%s/', $this->adminUrl);
        $adminRouteNamePrefixes = ['admin_', 'elfinder', 'ef_'];

        foreach ($routes as $routeName => $route) {
            if (str_starts_with($route->getPath(), $adminPathPrefix)) {
                $adminRoutes[$routeName] = $route;

                continue;
            }

            foreach ($adminRouteNamePrefixes as $adminRouteNamePrefix) {
                if (str_starts_with($routeName, $adminRouteNamePrefix)) {
                    $adminRoutes[$routeName] = $route;

                    continue 2;
                }
            }
        }

        return $adminRoutes;
    }

    /**
     * @param string $controller
     * @return array{0: class-string, 1: string}
     */
    private function parseController(string $controller): array
    {
        if (str_contains($controller, '::')) {
            return explode('::', $controller, 2);
        }

        if (str_contains($controller, ':')) {
            $parts = explode(':', $controller);

            if (count($parts) === 3) {
                throw new InvalidArgumentException('Bundle:Controller:Action format not supported');
            }
        }

        throw new InvalidArgumentException(sprintf('Invalid controller format: %s', $controller));
    }
}
