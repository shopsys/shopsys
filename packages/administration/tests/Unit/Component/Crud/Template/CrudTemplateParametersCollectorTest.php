<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud\Template;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Template\CrudTemplateParametersCollector;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;

final class CrudTemplateParametersCollectorTest extends TestCase
{
    public function testBaseParametersAreReturnedWithoutAnySource(): void
    {
        $parametersCollector = new CrudTemplateParametersCollector(ActionType::LIST, ['title' => 'Product reviews']);

        $this->assertSame(['title' => 'Product reviews'], $parametersCollector->getParameters());
    }

    public function testAdditionalParametersOfMultipleSourcesAreCollected(): void
    {
        $parametersCollector = new CrudTemplateParametersCollector(ActionType::EDIT, ['title' => 'Product review']);

        $parametersCollector->addAdditionalParameters(TestCollectorCrudController::class, ['gridView' => 'controller grid']);
        $parametersCollector->addAdditionalParameters(TestCollectorCrudControllerExtension::class, ['timeline' => 'extension timeline']);

        $this->assertSame([
            'title' => 'Product review',
            'gridView' => 'controller grid',
            'timeline' => 'extension timeline',
        ], $parametersCollector->getParameters());
    }

    public function testCollisionWithBaseParameterThrowsException(): void
    {
        $parametersCollector = new CrudTemplateParametersCollector(ActionType::EDIT, [
            'title' => 'Product review',
            'form' => 'Base form',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Additional template parameters of "%s" collide with the parameters of the "edit" action: "form" (a base parameter).',
            TestCollectorCrudController::class,
        ));

        $parametersCollector->addAdditionalParameters(TestCollectorCrudController::class, ['form' => 'Custom form']);
    }

    public function testCollisionWithParametersOfAnotherSourceThrowsException(): void
    {
        $parametersCollector = new CrudTemplateParametersCollector(ActionType::EDIT, ['title' => 'Product review']);
        $parametersCollector->addAdditionalParameters(TestCollectorCrudController::class, ['gridView' => 'controller grid']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Additional template parameters of "%s" collide with the parameters of the "edit" action: "gridView" (already added by "%s").',
            TestCollectorCrudControllerExtension::class,
            TestCollectorCrudController::class,
        ));

        $parametersCollector->addAdditionalParameters(TestCollectorCrudControllerExtension::class, ['gridView' => 'extension grid']);
    }

    public function testExceptionDescribesAllCollidingKeys(): void
    {
        $parametersCollector = new CrudTemplateParametersCollector(ActionType::EDIT, ['title' => 'Product review']);
        $parametersCollector->addAdditionalParameters(TestCollectorCrudController::class, ['gridView' => 'controller grid']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'collide with the parameters of the "edit" action: "title" (a base parameter), "gridView" (already added by "%s")',
            TestCollectorCrudController::class,
        ));

        $parametersCollector->addAdditionalParameters(TestCollectorCrudControllerExtension::class, [
            'title' => 'Custom title',
            'gridView' => 'extension grid',
        ]);
    }

    public function testCollidingSourceDoesNotChangeCollectedParameters(): void
    {
        $parametersCollector = new CrudTemplateParametersCollector(ActionType::EDIT, ['title' => 'Product review']);

        try {
            $parametersCollector->addAdditionalParameters(TestCollectorCrudController::class, [
                'title' => 'Custom title',
                'gridView' => 'controller grid',
            ]);
        } catch (InvalidArgumentException) {
            // the collected parameters must stay untouched
        }

        $this->assertSame(['title' => 'Product review'], $parametersCollector->getParameters());
    }
}

final class TestCollectorCrudController extends AbstractCrudController
{
}

final class TestCollectorCrudControllerExtension extends AbstractCrudControllerExtension
{
}
