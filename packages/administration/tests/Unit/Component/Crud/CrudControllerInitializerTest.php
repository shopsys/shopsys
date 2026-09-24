<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerInitializer;
use Shopsys\AdministrationBundle\Component\Crud\CrudDefinitionAwareInterface;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudController;
use Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures\ReviewCrudControllerExtension;

class CrudControllerInitializerTest extends TestCase
{
    public function testDefinitionIsSetOnAnyControllerHandlingCrudRouteAndOnExtensions(): void
    {
        $extension = new class() extends ReviewCrudControllerExtension {
            public function getDefinition(): Definition
            {
                return $this->definition;
            }
        };
        $initializer = new CrudControllerInitializer(ReviewCrudControllerRegistryFactory::create(extension: $extension));
        $controller = $this->createDefinitionAwareController();
        $request = new Request(attributes: [CrudRouteProvider::CRUD_CONTROLLER_CLASS => ReviewCrudController::class]);

        $initializer->onKernelController($this->createEvent($request, [$controller, 'setDefinition']));

        $this->assertInstanceOf(Definition::class, $controller->definition);
        $this->assertSame(ReviewCrudController::class, $controller->definition->controllerClass);
        $this->assertSame($controller->definition, $extension->getDefinition());
    }

    public function testCrudControllerRoutedByPlainRouteAttributeStillGetsDefinition(): void
    {
        $controller = new ReviewCrudController();
        $initializer = new CrudControllerInitializer(ReviewCrudControllerRegistryFactory::create(controller: $controller));

        $initializer->onKernelController($this->createEvent(new Request(), [$controller, 'approveAction']));

        $definition = (fn () => $this->definition)->call($controller);
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertSame(ReviewCrudController::class, $definition->controllerClass);
    }

    public function testUnrelatedControllerIsLeftAlone(): void
    {
        $initializer = new CrudControllerInitializer(ReviewCrudControllerRegistryFactory::create());
        $controller = $this->createDefinitionAwareController();

        $initializer->onKernelController($this->createEvent(new Request(), [$controller, 'setDefinition']));

        $this->assertNull($controller->definition);
    }

    /**
     * @param array{object, string} $controller
     */
    private function createEvent(Request $request, array $controller): ControllerEvent
    {
        return new ControllerEvent($this->createStub(HttpKernelInterface::class), $controller, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Crud\CrudDefinitionAwareInterface&object{definition: \Shopsys\AdministrationBundle\Component\Crud\Definition|null}
     */
    private function createDefinitionAwareController(): CrudDefinitionAwareInterface
    {
        return new class() implements CrudDefinitionAwareInterface {
            public ?Definition $definition = null;

            public function setDefinition(Definition $definition): void
            {
                $this->definition = $definition;
            }
        };
    }
}
