<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Controller\ProductReviewController;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class DatagridFactoryTest extends TestCase
{
    use SetTranslatorTrait;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
    }

    public function testDomainControlFollowsTheCrudDefinition(): void
    {
        $crudConfig = (new CrudConfig('Product', null, null, 'ProductCrud'))
            ->setListDomainControl(DomainControlType::FILTER, null, 'crud_test', 'domainId');
        $definition = new Definition(ProductReviewController::class, 'ProductCrud', stdClass::class, 'Product', $crudConfig->getConfig(), [], []);

        $config = $this->createFactory()->create(new CapturingAdapter($this->createStub(DataSourceInterface::class)), [
            'crudDefinition' => $definition,
            'roleConstant' => 'ROLE_CRUD_TEST',
        ])->getConfig();

        $this->assertSame($crudConfig->getConfig()->getDomainControlConfig(), $config->domainControlConfig);
    }

    public function testDomainControlGivenExplicitlyIsUsed(): void
    {
        $domainControlConfig = new DomainControlConfig(DomainControlType::SWITCHER);

        $config = $this->createFactory()->create(new CapturingAdapter($this->createStub(DataSourceInterface::class)), [
            'name' => 'Product',
            'roleConstant' => 'ROLE_CRUD_TEST',
            'domainControl' => $domainControlConfig,
        ])->getConfig();

        $this->assertSame($domainControlConfig, $config->domainControlConfig);
        $this->assertSame('Product', $config->gridId);
    }

    public function testDatagridOutsideCrudHasNoDomainControl(): void
    {
        $config = $this->createFactory()->create(new CapturingAdapter($this->createStub(DataSourceInterface::class)), [
            'roleConstant' => 'ROLE_CRUD_TEST',
        ])->getConfig();

        $this->assertFalse($config->domainControlConfig->isEnabled());
    }

    private function createFactory(): DatagridFactory
    {
        return new DatagridFactory($this->createStub(GridFactory::class), $this->createStub(AccessCheckerInterface::class), $this->createStub(AdministratorGridFacade::class), $this->createStub(Security::class));
    }
}
