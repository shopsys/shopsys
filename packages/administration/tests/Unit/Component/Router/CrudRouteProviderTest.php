<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Router;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Tests\AdministrationBundle\Unit\Component\Crud\ReviewCrudControllerRegistryFactory;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\RecalculateReviewsAction;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;

class CrudRouteProviderTest extends TestCase
{
    public function testBuiltInRoutesCarryTheCrudControllerClass(): void
    {
        $provider = $this->createProvider(ReviewCrudControllerRegistryFactory::create());

        $routeItem = $provider->getRouteItem(ReviewCrudController::class, ActionType::LIST);

        $this->assertSame('/review/', $routeItem->getRoute()->getPath());
        $this->assertSame(ReviewCrudController::class, $routeItem->getRoute()->getDefault(CrudRouteProvider::CRUD_CONTROLLER_CLASS));
        $this->assertSame(ReviewCrudController::class . '::listAction', $routeItem->getRoute()->getDefault('_controller'));
        $this->assertSame('list', $routeItem->getActionName());
    }

    public function testCustomActionRouteIsGeneratedUnderTheControllerUrl(): void
    {
        $provider = $this->createProvider(ReviewCrudControllerRegistryFactory::create());

        $routeItem = $provider->getRouteItem(ReviewCrudController::class, 'approve');
        $route = $routeItem->getRoute();

        $this->assertSame('admin_crud_review_approve', $routeItem->getRouteName());
        $this->assertSame('approve', $routeItem->getActionName());
        $this->assertSame('/review/approve/{id}', $route->getPath());
        $this->assertSame(ReviewCrudController::class . '::approveAction', $route->getDefault('_controller'));
        $this->assertTrue($route->getDefault(CrudRouteProvider::IS_CRUD_CONTROLLER));
        $this->assertSame('approve', $route->getDefault(CrudRouteProvider::CRUD_ACTION));
        $this->assertSame('ROLE_CRUD_REVIEW', $route->getDefault(CrudRouteProvider::CRUD_ROLE_CONSTANT));
        $this->assertSame(ReviewCrudController::class, $route->getDefault(CrudRouteProvider::CRUD_CONTROLLER_CLASS));
    }

    public function testRouteOptionsOfTheAttributeAreAppliedAndCrudDefaultsWin(): void
    {
        $controller = new class() extends ReviewCrudController {
            #[Override]
            public function configure(CrudConfig $config): void
            {
                $config->setRoutePrefix('/catalog');
            }
        };
        $provider = $this->createProvider(ReviewCrudControllerRegistryFactory::create([
            ReviewCrudControllerRegistryFactory::createActionData('download', [
                'method' => 'exportAction',
                'path' => '/export.{_format}',
                'entityBound' => false,
                'methods' => ['GET'],
                'requirements' => ['_format' => 'csv|xml'],
                'defaults' => ['_format' => 'csv', CrudRouteProvider::CRUD_ACTION => 'hijacked'],
                'condition' => 'request.isXmlHttpRequest()',
            ]),
        ], $controller));

        $route = $provider->getRouteItem(ReviewCrudController::class, 'download')->getRoute();

        $this->assertSame('/catalog/review/export.{_format}', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertSame(['_format' => 'csv|xml'], $route->getRequirements());
        $this->assertSame('csv', $route->getDefault('_format'));
        $this->assertSame('download', $route->getDefault(CrudRouteProvider::CRUD_ACTION));
        $this->assertSame('request.isXmlHttpRequest()', $route->getCondition());
    }

    public function testInvokableActionIsRoutedToItsInvokeMethod(): void
    {
        $provider = $this->createProvider(ReviewCrudControllerRegistryFactory::create([
            ReviewCrudControllerRegistryFactory::createActionData('recalculate', [
                'controllerClass' => RecalculateReviewsAction::class,
                'method' => '__invoke',
                'path' => '/recalculate',
                'entityBound' => false,
            ]),
        ]));

        $route = $provider->getRouteItem(ReviewCrudController::class, 'recalculate')->getRoute();

        $this->assertSame(RecalculateReviewsAction::class . '::__invoke', $route->getDefault('_controller'));
        $this->assertSame('/review/recalculate', $route->getPath());
    }

    public function testDisabledCustomActionHasNoRoute(): void
    {
        $controller = new class() extends ReviewCrudController {
            #[Override]
            public function configure(CrudConfig $config): void
            {
                $config->disableAction('approve');
            }
        };
        $provider = $this->createProvider(ReviewCrudControllerRegistryFactory::create([
            ReviewCrudControllerRegistryFactory::createActionData('reject'),
        ], $controller));

        // only LIST is enabled by default among the built-in actions
        $this->assertEqualsCanonicalizing(
            [ReviewCrudController::class . '::list', ReviewCrudController::class . '::move', ReviewCrudController::class . '::export_all', ReviewCrudController::class . '::reject'],
            array_keys($provider->getAll()),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('action "approve" not found');

        $provider->getRouteItem(ReviewCrudController::class, 'approve');
    }

    public function testFullyDisabledControllerHasNoRoutesAtAll(): void
    {
        $controller = new class() extends ReviewCrudController {
            #[Override]
            public function configure(CrudConfig $config): void
            {
                $config->disable(true);
            }
        };
        $provider = $this->createProvider(ReviewCrudControllerRegistryFactory::create(controller: $controller));

        $this->assertSame([], $provider->getAll());
    }

    private function createProvider(CrudControllerRegistry $registry): CrudRouteProvider
    {
        return new CrudRouteProvider($registry, new InMemoryCache());
    }
}
