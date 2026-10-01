<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Controller;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class CrudListDomainPresetTest extends TestCase
{
    public function testDomainSwitcherPresetsTheSelectedDomain(): void
    {
        $crudController = $this->createCrudController(CrudListDomainControl::SWITCHER, DomainPresetTestEntity::class);
        $data = new DomainPresetTestData();

        $crudController->presetSelectedListDomainForTest($data);

        $this->assertSame(2, $data->domainId);
    }

    public function testQuickFilterWithAllDomainsPresetsTheFirstDomainOfTheList(): void
    {
        $crudController = $this->createCrudController(CrudListDomainControl::QUICK_FILTER, DomainPresetTestEntity::class);
        $data = new DomainPresetTestData();

        $crudController->presetSelectedListDomainForTest($data);

        $this->assertSame(1, $data->domainId);
    }

    public function testQuickFilterPresetsTheSelectedDomain(): void
    {
        $crudController = $this->createCrudController(CrudListDomainControl::QUICK_FILTER, DomainPresetTestEntity::class, quickFilterSelectedDomainId: 3);
        $data = new DomainPresetTestData();

        $crudController->presetSelectedListDomainForTest($data);

        $this->assertSame(3, $data->domainId);
    }

    public function testDataObjectWithoutDomainIdIsLeftUntouched(): void
    {
        $crudController = $this->createCrudController(CrudListDomainControl::SWITCHER, DomainPresetTestEntity::class);
        $data = new DomainlessPresetTestData();

        $crudController->presetSelectedListDomainForTest($data);

        $this->assertSame(['name' => null], get_object_vars($data));
    }

    public function testEntityWithoutDomainIsLeftUntouched(): void
    {
        $crudController = $this->createCrudController(CrudListDomainControl::SWITCHER, stdClass::class);
        $data = new DomainPresetTestData();

        $crudController->presetSelectedListDomainForTest($data);

        $this->assertNull($data->domainId);
    }

    public function testListWithoutDomainControlIsLeftUntouched(): void
    {
        $crudController = $this->createCrudController(null, DomainPresetTestEntity::class);
        $data = new DomainPresetTestData();

        $crudController->presetSelectedListDomainForTest($data);

        $this->assertNull($data->domainId);
    }

    /**
     * @param class-string $entityClass
     */
    private function createCrudController(
        ?CrudListDomainControl $listDomainControl,
        string $entityClass,
        ?int $quickFilterSelectedDomainId = null,
    ): DomainPresetTestCrudController {
        $crudConfig = new CrudConfig('Store');

        if ($listDomainControl !== null) {
            $crudConfig->setListDomainControl($listDomainControl);
        }

        $crudController = new DomainPresetTestCrudController();
        $crudController->setDefinition(new Definition(
            DomainPresetTestCrudController::class,
            'DomainPresetTestCrudController',
            $entityClass,
            'Store',
            $crudConfig->getConfig(),
            [],
            [],
        ));
        $adminDomainTabsFacadeStub = $this->createStub(AdminDomainTabsFacade::class);
        $adminDomainTabsFacadeStub->method('getSelectedDomainId')->willReturn(2);
        $crudController->adminDomainTabsFacade = $adminDomainTabsFacadeStub;
        $adminDomainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn($quickFilterSelectedDomainId);
        $crudController->adminDomainFilterTabsFacade = $adminDomainFilterTabsFacadeStub;
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn([1, 3]);
        $crudController->domain = $domainStub;
        $crudController->propertyAccessor = PropertyAccess::createPropertyAccessor();

        return $crudController;
    }
}

final class DomainPresetTestCrudController extends AbstractCrudController
{
    public function presetSelectedListDomainForTest(object $data): void
    {
        $this->presetSelectedListDomain($data);
    }
}

final class DomainPresetTestEntity implements DomainSeparatedEntityInterface
{
    #[Override]
    public function getDomainId(): int
    {
        return 1;
    }
}

final class DomainPresetTestData
{
    public ?int $domainId = null;
}

final class DomainlessPresetTestData
{
    public ?string $name = null;
}
