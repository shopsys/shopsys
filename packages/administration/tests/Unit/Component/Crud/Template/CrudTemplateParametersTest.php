<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud\Template;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Crud\Template\CrudTemplateParameters;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;

final class CrudTemplateParametersTest extends TestCase
{
    public function testBaseParametersAreReturnedWithoutAnySource(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::LIST, ['title' => 'Product reviews']);

        $this->assertSame(['title' => 'Product reviews'], $templateParameters->toArray());
    }

    public function testParametersOfMultipleSourcesAreCollected(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
            $templateParameters
                ->set('gridView', 'controller grid')
                ->set('entityLogEntityName', 'productReview');
        });
        $templateParameters->collectFrom(TestCrudControllerExtension::class, function () use ($templateParameters): void {
            $templateParameters->set('timeline', 'extension timeline');
        });

        $this->assertSame([
            'title' => 'Product review',
            'gridView' => 'controller grid',
            'entityLogEntityName' => 'productReview',
            'timeline' => 'extension timeline',
        ], $templateParameters->toArray());
    }

    public function testBaseAndSetParametersCanBeRead(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);
        $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
            $templateParameters->set('gridView', 'controller grid');
        });

        $this->assertTrue($templateParameters->has('title'));
        $this->assertTrue($templateParameters->has('gridView'));
        $this->assertFalse($templateParameters->has('timeline'));
        $this->assertSame('Product review', $templateParameters->get('title'));
        $this->assertSame('controller grid', $templateParameters->get('gridView'));
    }

    public function testExtensionCanReadParametersOfControllerInsideItsSource(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);
        $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
            $templateParameters->set('gridView', 'controller grid');
        });

        $templateParameters->collectFrom(TestCrudControllerExtension::class, function () use ($templateParameters): void {
            $templateParameters->set('extendedGridView', $templateParameters->get('gridView') . ' extended');
        });

        $this->assertSame('controller grid extended', $templateParameters->get('extendedGridView'));
    }

    public function testGettingNotSetParameterThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template parameter "gridView" of the "edit" action is not set.');

        $templateParameters->get('gridView');
    }

    public function testSettingParameterOutsideOfSourceThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template parameters can be set only inside configureTemplateParameters()');

        $templateParameters->set('gridView', 'controller grid');
    }

    public function testCollisionWithBaseParameterThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, [
            'title' => 'Product review',
            'form' => 'Base form',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Template parameter "form" cannot be set by "%s" as it is a base parameter of the "edit" action. Choose a different name.',
            TestCrudController::class,
        ));

        $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
            $templateParameters->set('form', 'Custom form');
        });
    }

    public function testCollisionWithParameterOfAnotherSourceThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);
        $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
            $templateParameters->set('gridView', 'controller grid');
        });

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Template parameter "gridView" cannot be set by "%s" as it is already set by "%s" for the "edit" action. Choose a different name.',
            TestCrudControllerExtension::class,
            TestCrudController::class,
        ));

        $templateParameters->collectFrom(TestCrudControllerExtension::class, function () use ($templateParameters): void {
            $templateParameters->set('gridView', 'extension grid');
        });
    }

    public function testCollisionWithinTheSameSourceThrowsException(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Template parameter "gridView" cannot be set by "%s" as it is already set by "%s" for the "edit" action.',
            TestCrudController::class,
            TestCrudController::class,
        ));

        $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
            $templateParameters
                ->set('gridView', 'first grid')
                ->set('gridView', 'second grid');
        });
    }

    public function testCollidingSetDoesNotChangeCollectedParameters(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        try {
            $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
                $templateParameters->set('title', 'Custom title');
            });
        } catch (InvalidArgumentException) {
            // the collected parameters must stay untouched
        }

        $this->assertSame(['title' => 'Product review'], $templateParameters->toArray());
    }

    public function testSourceIsResetAfterFailedCollection(): void
    {
        $templateParameters = new CrudTemplateParameters(ActionType::EDIT, ['title' => 'Product review']);

        try {
            $templateParameters->collectFrom(TestCrudController::class, function () use ($templateParameters): void {
                $templateParameters->set('title', 'Custom title');
            });
        } catch (InvalidArgumentException) {
            // the source must not leak out of the failed collection
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template parameters can be set only inside configureTemplateParameters()');

        $templateParameters->set('gridView', 'controller grid');
    }
}

final class TestCrudController extends AbstractCrudController
{
}

final class TestCrudControllerExtension extends AbstractCrudControllerExtension
{
}
