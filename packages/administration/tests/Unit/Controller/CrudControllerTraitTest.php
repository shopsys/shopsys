<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\CrudControllerTrait;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CrudControllerTraitTest extends TestCase
{
    private const int ENTITY_ID = 42;
    private const string CSRF_TOKEN = 'csrf-token-value';

    public function testGetCrudRouteNameIsDerivedFromControllerName(): void
    {
        $controller = $this->createController();

        $this->assertSame('admin_crud_order_edit', $controller->getCrudRouteName(ActionType::EDIT));
    }

    public function testGenerateCrudUrlWithoutIdContainsOnlyAdditionalParameters(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::LIST, parameters: ['page' => 2]);

        $this->assertSame('/admin_crud_order_list?page=2', $url);
    }

    public function testGenerateCrudUrlWithIdAddsIdParameter(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::EDIT, self::ENTITY_ID);

        $this->assertSame('/admin_crud_order_edit?id=42', $url);
    }

    public function testGenerateCrudUrlWithEntityResolvesIdFromEntity(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::DETAIL, new stdClass());

        $this->assertSame('/admin_crud_order_detail?id=42', $url);
    }

    public function testGenerateCrudUrlForCsrfProtectedActionAddsCsrfToken(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::DELETE, self::ENTITY_ID);

        $this->assertSame('/admin_crud_order_delete?id=42&routeCsrfToken=' . self::CSRF_TOKEN, $url);
    }

    public function testGenerateCrudUrlKeepsExplicitlyPassedCsrfToken(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::DELETE, self::ENTITY_ID, [RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => 'custom']);

        $this->assertSame('/admin_crud_order_delete?routeCsrfToken=custom&id=42', $url);
    }

    public function testRedirectToCrudActionRedirectsToGeneratedUrl(): void
    {
        $controller = $this->createController();

        $response = $controller->redirectToCrudAction(ActionType::EDIT, self::ENTITY_ID);

        $this->assertSame('/admin_crud_order_edit?id=42', $response->getTargetUrl());
    }

    /**
     * Returns an object using the trait with generateUrl() and redirect() replaced by fakes,
     * so the generated URL is "/<routeName>?<query>" and can be asserted directly
     */
    private function createController(): object
    {
        $controller = new class() {
            use CrudControllerTrait {
                getCrudRouteName as public;
                generateCrudUrl as public;
                redirectToCrudAction as public;
            }

            /**
             * @param array<string, mixed> $parameters
             */
            protected function generateUrl(string $route, array $parameters = []): string
            {
                return '/' . $route . ($parameters === [] ? '' : '?' . http_build_query($parameters));
            }

            protected function redirect(string $url): RedirectResponse
            {
                return new RedirectResponse($url);
            }
        };

        $controller->setDefinition(new Definition(
            AbstractCrudController::class,
            'OrderController',
            stdClass::class,
            'Order',
            new CrudConfig('Order')->getConfig(),
            [],
            [],
        ));
        $controller->crudEntityIdentifierExtractor = new CrudEntityIdentifierExtractor($this->createManagerRegistryStub());
        $controller->routeCsrfProtector = new RouteCsrfProtector($this->createCsrfTokenManagerStub(), new InMemoryCache());

        return $controller;
    }

    private function createManagerRegistryStub(): ManagerRegistry
    {
        $classMetadata = $this->createStub(ClassMetadata::class);
        $classMetadata->method('getIdentifierFieldNames')->willReturn(['id']);
        $classMetadata->method('getIdentifierValues')->willReturn(['id' => self::ENTITY_ID]);

        $entityManager = $this->createStub(ObjectManager::class);
        $entityManager->method('getClassMetadata')->willReturn($classMetadata);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        return $managerRegistry;
    }

    private function createCsrfTokenManagerStub(): CsrfTokenManagerInterface
    {
        $tokenManager = $this->createStub(CsrfTokenManagerInterface::class);
        $tokenManager->method('getToken')->willReturnCallback(
            static fn (string $tokenId): CsrfToken => new CsrfToken($tokenId, self::CSRF_TOKEN),
        );

        return $tokenManager;
    }
}
