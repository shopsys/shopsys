<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use Override;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Crud\Template\CrudTemplateParameters;
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

    public function testConfiguredTemplateIsRenderedWithParametersFromEntity(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setTemplate(ActionType::EDIT, '@ShopsysAdministration/content/productReview/edit.html.twig');
        $crudController = $this->createCrudController([], null, $crudConfig);
        $twigMock = $this->createMock(Environment::class);
        $twigMock
            ->expects($this->once())
            ->method('render')
            ->with('@ShopsysAdministration/content/productReview/edit.html.twig', [
                'title' => 'Base title',
                'entityName' => 'Review #1',
            ])
            ->willReturn('');
        $crudController->setContainer($this->createContainerStub($twigMock));

        $crudController->renderActionForTest(ActionType::EDIT, ['title' => 'Base title'], new TestRenderEntity());
    }

    /**
     * @param array<string, mixed> $additionalTemplateParameters
     */
    private function createCrudController(
        array $additionalTemplateParameters,
        ?AbstractCrudControllerExtension $extension = null,
        ?CrudConfig $crudConfig = null,
    ): TestRenderCrudController {
        $crudController = new TestRenderCrudController();
        $crudController->additionalTemplateParameters = $additionalTemplateParameters;
        $crudController->setDefinition(new Definition(
            TestRenderCrudController::class,
            'TestRenderCrudController',
            stdClass::class,
            'Product review',
            ($crudConfig ?? new CrudConfig('Product review'))->getConfig(),
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
    public function renderActionForTest(
        ActionType $actionType,
        array $parameters,
        ?Presentable $entity = null,
    ): Response {
        return $this->renderAction($actionType, $parameters, $entity);
    }

    #[Override]
    protected function configureTemplateParameters(CrudTemplateParameters $templateParameters): void
    {
        foreach ($this->additionalTemplateParameters as $name => $value) {
            $templateParameters->set($name, $value);
        }

        if ($templateParameters->hasEntity()) {
            $templateParameters->set('entityName', $templateParameters->getEntity(TestRenderEntity::class)->toHumanReadable());
        }
    }
}

final class TestRenderCrudControllerExtension extends AbstractCrudControllerExtension
{
    /**
     * @var array<string, mixed>
     */
    public array $additionalTemplateParameters = [];

    #[Override]
    public function configureTemplateParameters(CrudTemplateParameters $templateParameters): void
    {
        foreach ($this->additionalTemplateParameters as $name => $value) {
            $templateParameters->set($name, $value);
        }
    }
}

final class TestRenderEntity implements Presentable
{
    #[Override]
    public function toHumanReadable(): string
    {
        return 'Review #1';
    }
}
