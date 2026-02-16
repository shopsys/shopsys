<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\AccessControl;

use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleFactory;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlDataProvider;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouter;
use Shopsys\FrameworkBundle\Component\Router\AdministrationRouterFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Contracts\Cache\CacheInterface;

class RouteAccessControlDataProviderTest extends TestCase
{
    private CacheInterface&Stub $cache;

    private AdministrationRouterFactory&Stub $administrationRouterFactory;

    private LoggerInterface&Stub $logger;

    private AttributeProcessor $attributeProcessor;

    private AdministrationRouter&Stub $router;

    private RouteCollection $routeCollection;

    private RoleRegistryInterface&Stub $roleRegistry;

    private AccessControlRuleFactory $accessControlRuleFactory;

    #[Override]
    protected function setUp(): void
    {
        $this->cache = $this->createStub(CacheInterface::class);
        $this->administrationRouterFactory = $this->createStub(AdministrationRouterFactory::class);
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->router = $this->createStub(AdministrationRouter::class);
        $this->routeCollection = new RouteCollection(); // Use real RouteCollection
        $this->roleRegistry = $this->createStub(RoleRegistryInterface::class);

        // Create real AttributeProcessor with mocked dependencies
        $accessControlRuleFactory = new AccessControlRuleFactory($this->roleRegistry);
        $this->accessControlRuleFactory = $accessControlRuleFactory;
        $this->attributeProcessor = new AttributeProcessor($accessControlRuleFactory);

        // Set up role registry to return stub roles for any identifier
        $this->roleRegistry
            ->method('getRole')
            ->willReturnCallback(function (string $identifier, string $context) {
                $role = $this->createStub(Role::class);
                $role->method('getConstant')->willReturn($identifier);

                return $role;
            });

        $this->administrationRouterFactory
            ->method('getAdministrationRouter')
            ->willReturn($this->router);

        // Router will return the current route collection (updated by setupRouteCollection)
        $this->router
            ->method('getRouteCollection')
            ->willReturnCallback(function () {
                return $this->routeCollection;
            });
    }

    public function testGetAllInDevelopmentEnvironment(): void
    {
        $this->setupRouteCollection([
            'admin_test' => $this->createRoute('/admin/test', null), // Admin route
            'public_test' => $this->createRoute('/public/test', null), // Non-admin route
        ]);

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->getAll();

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('admin_test', $result);
        $this->assertInstanceOf(RouteAccessControlData::class, $result['admin_test']);
    }

    public function testGetAllInProductionEnvironmentUsesCache(): void
    {
        $cachedData = [
            'admin_cached' => new RouteAccessControlData('admin_cached', [], 'CachedController', 'cachedAction'),
        ];

        $cacheMock = $this->createMock(CacheInterface::class);
        $cacheMock
            ->expects($this->once())
            ->method('get')
            ->with('shopsys_access_control_rules')
            ->willReturn($cachedData);

        $provider = $this->createProvider(EnvironmentType::PRODUCTION, $cacheMock);

        $result = $provider->getAll();

        $this->assertSame($cachedData, $result);
    }

    public function testFindRouteByNameReturnsExistingRoute(): void
    {
        $this->setupRouteCollection([
            'admin_found' => $this->createRoute('/admin/found', 'FoundController::foundAction'),
        ]);

        // AttributeProcessor will process methods with real logic

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->findRouteByName('admin_found');

        $this->assertInstanceOf(RouteAccessControlData::class, $result);
        $this->assertEquals('admin_found', $result->routeName);
    }

    public function testFindRouteByNameReturnsNullForNonExistentRoute(): void
    {
        $this->setupRouteCollection([]);

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->findRouteByName('non_existent');

        $this->assertNull($result);
    }

    public function testOnlyAdminRoutesAreProcessed(): void
    {
        $this->setupRouteCollection([
            'admin_included' => $this->createRoute('/admin/included', 'IncludedController::action'),
            'public_excluded' => $this->createRoute('/public/excluded', 'ExcludedController::action'),
            'admin_prefixed_route' => $this->createRoute('/other/path', 'PrefixedController::action'),
        ]);

        // AttributeProcessor will process methods with real logic

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->getAll();

        $this->assertCount(2, $result); // Only admin routes
        $this->assertArrayHasKey('admin_included', $result);
        $this->assertArrayHasKey('admin_prefixed_route', $result);
        $this->assertArrayNotHasKey('public_excluded', $result);
    }

    public function testValidControllerParsingCreatesCorrectData(): void
    {
        $this->setupRouteCollection([
            'admin_valid' => $this->createRoute('/admin/valid', 'App\\Controller\\TestController::testAction'),
        ]);

        // AttributeProcessor will process methods with real logic

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->getAll();

        $routeData = $result['admin_valid'];
        $this->assertEquals('App\\Controller\\TestController', $routeData->controllerClass);
        $this->assertEquals('testAction', $routeData->controllerMethod);
        // Without actual attributes on the method, rules will be empty
        // Since we're using a real method without attributes, rules will be empty
        $this->assertEmpty($routeData->accessControlRules);
    }

    public function testInvalidControllerFormatLogsErrorAndCreatesEmptyData(): void
    {
        $this->setupRouteCollection([
            'admin_invalid' => $this->createRoute('/admin/invalid', 'invalid_controller_format'),
        ]);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error')
            ->with(
                'Skipping route with invalid controller or method',
                $this->callback(function (array $context) {
                    return $context['route_name'] === 'admin_invalid' &&
                           $context['controller'] === 'invalid_controller_format' &&
                           isset($context['exception_message']) &&
                           isset($context['exception_type']);
                }),
            );

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT, logger: $loggerMock);
        $result = $provider->getAll();

        $routeData = $result['admin_invalid'];
        $this->assertEquals('invalid_controller_format', $routeData->controllerClass);
        $this->assertEquals('unknownMethod', $routeData->controllerMethod);
        $this->assertEmpty($routeData->accessControlRules);
    }

    public function testNonExistentControllerClassCreatesEmptyData(): void
    {
        $this->setupRouteCollection([
            'admin_missing_class' => $this->createRoute('/admin/missing', 'NonExistent\\Controller::action'),
        ]);

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->getAll();

        $routeData = $result['admin_missing_class'];
        $this->assertEquals('NonExistent\\Controller', $routeData->controllerClass);
        $this->assertEquals('action', $routeData->controllerMethod);
        $this->assertEmpty($routeData->accessControlRules);
    }

    public function testReflectionExceptionLogsErrorAndCreatesEmptyData(): void
    {
        $this->setupRouteCollection([
            'admin_reflection_error' => $this->createRoute('/admin/reflection', 'Tests\\AdministrationBundle\\Unit\\Component\\Security\\AccessControl\\RouteAccessControlDataProviderTest::nonExistentMethod'),
        ]);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error')
            ->with(
                'Skipping route with invalid controller or method',
                $this->callback(function (array $context) {
                    return $context['route_name'] === 'admin_reflection_error' &&
                           str_contains($context['exception_type'], 'ReflectionException');
                }),
            );

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT, logger: $loggerMock);
        $result = $provider->getAll();

        $routeData = $result['admin_reflection_error'];
        $this->assertEquals('Tests\\AdministrationBundle\\Unit\\Component\\Security\\AccessControl\\RouteAccessControlDataProviderTest', $routeData->controllerClass);
        $this->assertEquals('nonExistentMethod', $routeData->controllerMethod);
        $this->assertEmpty($routeData->accessControlRules);
    }

    public function testMissingControllerDefaultCreatesUnknownData(): void
    {
        $route = new Route('/admin/no-controller');

        $this->setupRouteCollection(['admin_no_controller' => $route]);

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->getAll();

        $routeData = $result['admin_no_controller'];
        $this->assertEquals('UnknownController', $routeData->controllerClass);
        $this->assertEquals('unknownMethod', $routeData->controllerMethod);
        $this->assertEmpty($routeData->accessControlRules);
    }

    public function testNonStringControllerDefaultCreatesUnknownData(): void
    {
        $route = new Route('/admin/array-controller', ['_controller' => ['not', 'a', 'string']]);

        $this->setupRouteCollection(['admin_array_controller' => $route]);

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT);
        $result = $provider->getAll();

        $routeData = $result['admin_array_controller'];
        $this->assertEquals('UnknownController', $routeData->controllerClass);
        $this->assertEquals('unknownMethod', $routeData->controllerMethod);
        $this->assertEmpty($routeData->accessControlRules);
    }

    public function testBundleControllerActionFormatThrowsException(): void
    {
        $this->setupRouteCollection([
            'admin_bundle_format' => $this->createRoute('/admin/bundle', 'AppBundle:Controller:action'),
        ]);

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error')
            ->with(
                'Skipping route with invalid controller or method',
                $this->callback(function (array $context) {
                    return str_contains($context['exception_message'], 'Bundle:Controller:Action format not supported');
                }),
            );

        $provider = $this->createProvider(EnvironmentType::DEVELOPMENT, logger: $loggerMock);
        $result = $provider->getAll();

        $routeData = $result['admin_bundle_format'];
        $this->assertEquals('AppBundle:Controller:action', $routeData->controllerClass);
        $this->assertEquals('unknownMethod', $routeData->controllerMethod);
        $this->assertEmpty($routeData->accessControlRules);
    }

    private function createProvider(
        string $environment,
        ?CacheInterface $cache = null,
        ?LoggerInterface $logger = null,
    ): RouteAccessControlDataProvider {
        return new RouteAccessControlDataProvider(
            $cache ?? $this->cache,
            $environment,
            $this->administrationRouterFactory,
            $logger ?? $this->logger,
            'admin',
            $this->attributeProcessor,
            $this->accessControlRuleFactory,
        );
    }

    private function createRoute(string $path, ?string $controller): Route
    {
        $defaults = [];

        if ($controller !== null) {
            $defaults['_controller'] = $controller;
        }

        return new Route($path, $defaults);
    }

    /**
     * @param array<string, \Symfony\Component\Routing\Route> $routes
     */
    private function setupRouteCollection(array $routes): void
    {
        // Clear existing routes
        $this->routeCollection = new RouteCollection();

        // Add routes to the collection
        foreach ($routes as $name => $route) {
            $this->routeCollection->add($name, $route);
        }
    }
}
