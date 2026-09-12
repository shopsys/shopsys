<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfig;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableEntityConfigFactory;
use stdClass;

final class CrudEntityLogTest extends TestCase
{
    public function testLoggableEntityShowsTheEntityLog(): void
    {
        $crudController = $this->createCrudController(isEntityLoggable: true);

        $this->assertTrue($crudController->isEntityLogShownForTest());
    }

    public function testEntityWithoutLoggingDoesNotShowTheEntityLog(): void
    {
        $crudController = $this->createCrudController(isEntityLoggable: false);

        $this->assertFalse($crudController->isEntityLogShownForTest());
    }

    private function createCrudController(bool $isEntityLoggable): EntityLogTestCrudController
    {
        $crudController = new EntityLogTestCrudController();
        $crudController->setDefinition(new Definition(
            EntityLogTestCrudController::class,
            'EntityLogTestCrudController',
            stdClass::class,
            'Product review',
            (new CrudConfig('Product review'))->getConfig(),
            [],
            [],
        ));

        $loggableEntityConfigStub = $this->createStub(LoggableEntityConfig::class);
        $loggableEntityConfigStub->method('isLoggable')->willReturn($isEntityLoggable);
        $loggableEntityConfigFactoryStub = $this->createStub(LoggableEntityConfigFactory::class);
        $loggableEntityConfigFactoryStub->method('getLoggableSetupByEntity')->willReturn($loggableEntityConfigStub);
        $crudController->loggableEntityConfigFactory = $loggableEntityConfigFactoryStub;

        return $crudController;
    }
}

final class EntityLogTestCrudController extends AbstractCrudController
{
    public function isEntityLogShownForTest(): bool
    {
        return $this->isEntityLogShown();
    }
}
