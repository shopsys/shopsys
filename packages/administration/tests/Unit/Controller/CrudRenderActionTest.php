<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use stdClass;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class CrudRenderActionTest extends TestCase
{
    public function testAdditionalParametersOfControllerAndExtensionAreRendered(): void
    {
        $extension = new TestRenderCrudControllerExtension();
        $extension->additionalTemplateParameters = ['timeline' => 'extension timeline'];
        $crudController = $this->createCrudController(['gridView' => 'controller grid'], $extension);
        $twigMock = $this->createMock(Environment::class);
        $twigMock
            ->expects($this->once())
            ->method('render')
            ->with('@ShopsysAdministration/crud/list.html.twig', [
                'title' => 'Base title',
                'gridView' => 'controller grid',
                'timeline' => 'extension timeline',
            ])
            ->willReturn('');
        $crudController->setContainer($this->createContainerStub($twigMock));

        $crudController->renderActionForTest(ActionType::LIST, ['title' => 'Base title']);
    }

    public function testControllerParameterCollidingWithBaseParameterThrowsException(): void
    {
        $crudController = $this->createCrudController(['title' => 'Custom title']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Additional template parameters of "%s" collide with the parameters of the "list" action: "title" (a base parameter).',
            TestRenderCrudController::class,
        ));

        $crudController->renderActionForTest(ActionType::LIST, ['title' => 'Base title']);
    }

    public function testExtensionParameterCollidingWithBaseParameterThrowsException(): void
    {
        $extension = new TestRenderCrudControllerExtension();
        $extension->additionalTemplateParameters = ['form' => 'Custom form'];
        $crudController = $this->createCrudController([], $extension);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Additional template parameters of "%s" collide with the parameters of the "edit" action: "form" (a base parameter).',
            TestRenderCrudControllerExtension::class,
        ));

        $crudController->renderActionForTest(ActionType::EDIT, ['title' => 'Base title', 'form' => 'Base form']);
    }

    public function testExtensionParameterCollidingWithControllerParameterThrowsException(): void
    {
        $extension = new TestRenderCrudControllerExtension();
        $extension->additionalTemplateParameters = ['gridView' => 'extension grid'];
        $crudController = $this->createCrudController(['gridView' => 'controller grid'], $extension);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Additional template parameters of "%s" collide with the parameters of the "list" action: "gridView" (already added by "%s").',
            TestRenderCrudControllerExtension::class,
            TestRenderCrudController::class,
        ));

        $crudController->renderActionForTest(ActionType::LIST, ['title' => 'Base title']);
    }

    /**
     * @param array<string, mixed> $additionalTemplateParameters
     */
    private function createCrudController(
        array $additionalTemplateParameters,
        ?AbstractCrudControllerExtension $extension = null,
    ): TestRenderCrudController {
        $crudController = new TestRenderCrudController();
        $crudController->additionalTemplateParameters = $additionalTemplateParameters;
        $crudController->setDefinition(new Definition(
            TestRenderCrudController::class,
            'TestRenderCrudController',
            stdClass::class,
            'Product review',
            (new CrudConfig('Product review'))->getConfig(),
            $extension !== null ? [$extension] : [],
            [],
        ));

        return $crudController;
    }

    private function createContainerStub(Environment $twig): ContainerInterface
    {
        $containerStub = $this->createStub(ContainerInterface::class);
        $containerStub->method('has')->willReturnCallback(static fn (string $id): bool => $id === 'twig');
        $containerStub->method('get')->willReturnCallback(
            static fn (string $id): Environment => $id === 'twig' ? $twig : throw new ServiceNotFoundException($id),
        );

        return $containerStub;
    }
}

final class TestRenderCrudController extends AbstractCrudController
{
    /**
     * @var array<string, mixed>
     */
    public array $additionalTemplateParameters = [];

    /**
     * @param array<string, mixed> $parameters
     */
    public function renderActionForTest(ActionType $actionType, array $parameters): Response
    {
        return $this->renderAction($actionType, $parameters);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getAdditionalTemplateParameters(ActionType $actionType, ?Presentable $entity = null): array
    {
        return $this->additionalTemplateParameters;
    }
}

final class TestRenderCrudControllerExtension extends AbstractCrudControllerExtension
{
    /**
     * @var array<string, mixed>
     */
    public array $additionalTemplateParameters = [];

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function getAdditionalTemplateParameters(ActionType $actionType, ?Presentable $entity = null): array
    {
        return $this->additionalTemplateParameters;
    }
}
