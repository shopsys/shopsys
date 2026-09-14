<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Controller\ProductReviewController;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * The factory reads the state of a datagrid from the request and its domain control from the CRUD definition,
 * unless the caller gives them explicitly.
 */
final class DatagridFactoryTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);

        Translator::injectSelf($translatorStub);
    }

    public function testStateIsReadFromTheMainRequest(): void
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/', 'GET', [
            DatagridNarrowingResolverFactory::GRID_ID . '_search' => [
                'text' => 'hrnek',
            ],
        ]));

        $datagrid = $this->createSearchableDatagrid($this->createFactory($requestStack));

        $this->assertSame('hrnek', $datagrid->getNarrowing()->quickSearch?->term);
    }

    public function testDatagridBuiltOutsideRequestOffersNoQuickSearch(): void
    {
        $datagrid = $this->createSearchableDatagrid($this->createFactory(new RequestStack()));

        $this->assertNull($datagrid->getNarrowing()->quickSearch);
    }

    public function testStateAndDomainControlGivenExplicitlyAreUsed(): void
    {
        $domainControlScope = new DomainControlScope(DomainControlType::FILTER, [1, 2], 1, 'crud_test', 'domainId');

        $datagrid = $this->createFactory(new RequestStack())->create(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            [
                'name' => DatagridNarrowingResolverFactory::GRID_ID,
                'roleConstant' => 'ROLE_CRUD_TEST',
            ],
            DatagridNarrowingResolverFactory::createRequestState(['text' => 'hrnek']),
            $domainControlScope,
        );
        $datagrid->add('name', ['searchable' => true]);

        $this->assertSame($domainControlScope, $datagrid->getDomainControlScope());
        $this->assertSame('hrnek', $datagrid->getNarrowing()->quickSearch?->term);
    }

    public function testDomainControlFollowsTheCrudDefinition(): void
    {
        $crudConfig = (new CrudConfig('Product', null, null, 'ProductCrud'))
            ->setListDomainControl(DomainControlType::FILTER, null, 'crud_test', 'domainId');
        $definition = new Definition(ProductReviewController::class, 'ProductCrud', stdClass::class, 'Product', $crudConfig->getConfig(), [], []);

        $datagrid = $this->createFactory(new RequestStack(), 2)->create(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            [
                'crudDefinition' => $definition,
                'roleConstant' => 'ROLE_CRUD_TEST',
            ],
        );

        $domainControlScope = $datagrid->getDomainControlScope();
        $this->assertNotNull($domainControlScope);
        $this->assertSame(DomainControlType::FILTER, $domainControlScope->type);
        $this->assertSame(2, $domainControlScope->selectedDomainId);
        $this->assertSame('crud_test', $domainControlScope->filterNamespace);
        $this->assertSame('domainId', $domainControlScope->domainIdPath);
    }

    public function testDatagridOutsideCrudHasNoDomainControl(): void
    {
        $datagrid = $this->createFactory(new RequestStack())->create(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            ['roleConstant' => 'ROLE_CRUD_TEST'],
        );

        $this->assertNull($datagrid->getDomainControlScope());
    }

    private function createSearchableDatagrid(DatagridFactory $datagridFactory): Datagrid
    {
        $datagrid = $datagridFactory->create(new CapturingAdapter($this->createStub(DataSourceInterface::class)), [
            'name' => DatagridNarrowingResolverFactory::GRID_ID,
            'roleConstant' => 'ROLE_CRUD_TEST',
        ]);
        $datagrid->add('name', ['searchable' => true]);

        return $datagrid;
    }

    private function createFactory(RequestStack $requestStack, ?int $selectedDomainId = null): DatagridFactory
    {
        $adminDomainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn($selectedDomainId);
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn([1, 2, 3]);

        return new DatagridFactory(
            $this->createStub(GridFactory::class),
            $this->createStub(AccessCheckerInterface::class),
            $this->createStub(AdministratorGridFacade::class),
            $this->createStub(Security::class),
            new DomainControlScopeFactory($adminDomainFilterTabsFacadeStub, $this->createStub(AdminDomainTabsFacade::class), $domainStub),
            DatagridNarrowingResolverFactory::create(),
            $requestStack,
        );
    }
}
