<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Action\Exception\CrudActionNotFoundException;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Handler\ReadHandlerInterface;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Controller\CrudControllerTrait;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Router\Security\RouteCsrfProtector;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Tests\AdministrationBundle\Unit\Component\Crud\ReviewCrudControllerRegistryFactory;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class CrudControllerTraitTest extends TestCase
{
    private const int ENTITY_ID = 42;
    private const string CSRF_TOKEN = 'csrf-token-value';

    public function testGetCrudRouteNameIsDerivedFromControllerNameForBuiltInAndCustomActions(): void
    {
        $controller = $this->createController();

        $this->assertSame('admin_crud_review_edit', $controller->getCrudRouteName(ActionType::EDIT));
        $this->assertSame('admin_crud_review_approve', $controller->getCrudRouteName('approve'));
    }

    public function testGenerateCrudUrlWithoutIdContainsOnlyAdditionalParameters(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::LIST, parameters: ['page' => 2]);

        $this->assertSame('/admin_crud_review_list?page=2', $url);
    }

    public function testGenerateCrudUrlWithIdAddsIdParameter(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::EDIT, self::ENTITY_ID);

        $this->assertSame('/admin_crud_review_edit?id=42', $url);
    }

    public function testGenerateCrudUrlWithEntityResolvesIdFromEntity(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::DETAIL, $this->createEntity());

        $this->assertSame('/admin_crud_review_detail?id=42', $url);
    }

    public function testGenerateCrudUrlForCsrfProtectedActionsAddsCsrfToken(): void
    {
        $controller = $this->createController();

        $this->assertSame(
            '/admin_crud_review_delete?id=42&routeCsrfToken=' . self::CSRF_TOKEN,
            $controller->generateCrudUrl(ActionType::DELETE, self::ENTITY_ID),
        );
        // the custom approve action of the fixture controller is CSRF protected as well
        $this->assertSame(
            '/admin_crud_review_approve?id=42&routeCsrfToken=' . self::CSRF_TOKEN,
            $controller->generateCrudUrl('approve', self::ENTITY_ID),
        );
    }

    public function testGenerateCrudUrlKeepsExplicitlyPassedCsrfToken(): void
    {
        $controller = $this->createController();

        $url = $controller->generateCrudUrl(ActionType::DELETE, self::ENTITY_ID, [RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => 'custom']);

        $this->assertSame('/admin_crud_review_delete?routeCsrfToken=custom&id=42', $url);
    }

    public function testGenerateCrudUrlRejectsMissingIdForEntityBoundAction(): void
    {
        $controller = $this->createController();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Action "approve" of "' . ReviewCrudController::class . '" works with a single record');

        $controller->generateCrudUrl('approve');
    }

    public function testGenerateCrudUrlRejectsIdForActionWithoutRecord(): void
    {
        $controller = $this->createController();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not work with a single record');

        $controller->generateCrudUrl(ActionType::LIST, self::ENTITY_ID);
    }

    public function testGenerateCrudUrlRejectsUnknownAction(): void
    {
        $controller = $this->createController();

        $this->expectException(CrudActionNotFoundException::class);

        $controller->generateCrudUrl('publish');
    }

    public function testRedirectToCrudActionRedirectsToGeneratedUrl(): void
    {
        $controller = $this->createController();

        $response = $controller->redirectToCrudAction('approve', self::ENTITY_ID);

        $this->assertSame('/admin_crud_review_approve?id=42&routeCsrfToken=' . self::CSRF_TOKEN, $response->getTargetUrl());
    }

    public function testGetCrudEntityLoadsRecordThroughRegisteredHandler(): void
    {
        $entity = $this->createEntity();
        $readHandler = $this->createStub(ReadHandlerInterface::class);
        $readHandler->method('getById')->willReturnCallback(static fn (int $id): Presentable => $id === self::ENTITY_ID ? $entity : throw new InvalidArgumentException());
        $controller = $this->createController($readHandler);

        $this->assertSame($entity, $controller->getCrudEntity(self::ENTITY_ID));
    }

    /**
     * Returns an object using the trait with generateUrl() and redirect() replaced by fakes,
     * so the generated URL is "/<routeName>?<query>" and can be asserted directly
     */
    private function createController(?ReadHandlerInterface $readHandler = null): object
    {
        $controller = new class() {
            use CrudControllerTrait {
                getCrudRouteName as public;
                generateCrudUrl as public;
                redirectToCrudAction as public;
                getCrudEntity as public;
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
            ReviewCrudController::class,
            'ReviewCrudController',
            $this->createEntity()::class,
            'Review',
            new CrudConfig('Review', customActionNames: ['approve', 'export_all'])->getConfig(),
            [],
            $readHandler === null ? [] : [ActionType::EDIT->value => $readHandler],
            ReviewCrudControllerRegistryFactory::createActionDefinitions(),
        ));
        $controller->crudEntityIdentifierExtractor = new CrudEntityIdentifierExtractor($this->createManagerRegistryStub());
        $controller->routeCsrfProtector = new RouteCsrfProtector($this->createCsrfTokenManagerStub(), new InMemoryCache());

        return $controller;
    }

    private function createEntity(): Presentable
    {
        return new class() implements Presentable {
            public function toHumanReadable(): string
            {
                return 'review';
            }
        };
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
