<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Config;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ManyToOneAssociationMapping;
use Doctrine\ORM\Mapping\OneToManyAssociationMapping;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use LogicException;
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

final class CrudListDomainControlTest extends TestCase
{
    public function testQuickDomainFilterConfigurationIsStored(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, [1, 3]);

        $crudConfigData = $crudConfig->getConfig();

        $this->assertSame(CrudListDomainControl::QUICK_FILTER, $crudConfigData->getListDomainControl());
        $this->assertSame([1, 3], $crudConfigData->getListAllowedDomainIds());
    }

    public function testDomainSwitcherConfigurationIsStored(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $crudConfig->setListDomainControl(CrudListDomainControl::SWITCHER);

        $crudConfigData = $crudConfig->getConfig();

        $this->assertSame(CrudListDomainControl::SWITCHER, $crudConfigData->getListDomainControl());
        $this->assertNull($crudConfigData->getListAllowedDomainIds());
    }

    public function testDomainIdFieldConfigurationIsStored(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, domainIdField: 'domains.domainId');

        $this->assertSame('domains.domainId', $crudConfig->getConfig()->getListDomainIdField());
    }

    public function testInvalidDomainIdFieldThrowsException(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain ID field "domains.article.domainId" is not valid');

        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, domainIdField: 'domains.article.domainId');
    }

    public function testDomainSwitcherWithAllowedDomainIdsThrowsException(): void
    {
        $crudConfig = new CrudConfig('Product review');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Domain switcher does not support allowed domain IDs.');

        $crudConfig->setListDomainControl(CrudListDomainControl::SWITCHER, [1, 3]);
    }

    public function testSelectedListDomainIdUsesNamespaceGeneratedFromControllerName(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER);
        $crudController = $this->createCrudController($crudConfig);
        $domainFilterTabsFacadeMock = $this->createMock(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->with('crud_test', [1, 2, 3])
            ->willReturn(2);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeMock;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);

        $selectedDomainId = $crudController->getSelectedListDomainIdForTest();

        $this->assertSame(2, $selectedDomainId);
    }

    public function testSelectedListDomainIdUsesDomainSwitcher(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::SWITCHER);
        $crudController = $this->createCrudController($crudConfig);
        $domainTabsFacadeMock = $this->createMock(AdminDomainTabsFacade::class);
        $domainTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->willReturn(3);
        $crudController->adminDomainFilterTabsFacade = $this->createStub(AdminDomainFilterTabsFacade::class);
        $crudController->adminDomainTabsFacade = $domainTabsFacadeMock;

        $selectedDomainId = $crudController->getSelectedListDomainIdForTest();

        $this->assertSame(3, $selectedDomainId);
    }

    public function testSelectedListDomainIdIsRestrictedByListDomainIds(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, [1, 3]);
        $crudController = $this->createCrudController($crudConfig, [1, 2, 3]);
        $domainFilterTabsFacadeMock = $this->createMock(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeMock
            ->expects($this->once())
            ->method('getSelectedDomainId')
            ->with('crud_test', [1, 3])
            ->willReturn(null);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeMock;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);

        $selectedDomainId = $crudController->getSelectedListDomainIdForTest();

        $this->assertNull($selectedDomainId);
    }

    public function testListDomainIdsAreLimitedByAllowedAndAdminEnabledDomains(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, [1, 3, 4]);
        $crudController = $this->createCrudController($crudConfig, [1, 2, 3]);

        $this->assertSame([1, 3], $crudController->getListDomainIdsForTest());
    }

    public function testSelectedListDomainIdWithoutConfiguredControlThrowsException(): void
    {
        $crudController = $this->createCrudController(new CrudConfig('Product review'));
        $crudController->adminDomainFilterTabsFacade = $this->createStub(AdminDomainFilterTabsFacade::class);
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('List domain control is not configured.');

        $crudController->getSelectedListDomainIdForTest();
    }

    public function testListDomainFilterAppliesSelectedDomain(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER);
        $crudController = $this->createCrudController($crudConfig, [1, 2, 3], TestDomainSeparatedEntity::class);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(2);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString('o.domainId IN (:listDomainFilterDomainIds)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame([2], $queryBuilder->getParameter('listDomainFilterDomainIds')->getValue());
    }

    public function testListDomainFilterAppliesAllDomains(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, [1, 3, 4]);
        $crudController = $this->createCrudController($crudConfig, [1, 2, 3], TestDomainSeparatedEntity::class);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(null);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString('o.domainId IN (:listDomainFilterDomainIds)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame([1, 3], $queryBuilder->getParameter('listDomainFilterDomainIds')->getValue());
    }

    public function testListDomainFilterAppliesDomainSwitcherSelection(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::SWITCHER);
        $crudController = $this->createCrudController($crudConfig, [1, 2, 3], TestDomainSeparatedEntity::class);
        $domainTabsFacadeStub = $this->createStub(AdminDomainTabsFacade::class);
        $domainTabsFacadeStub->method('getSelectedDomainId')->willReturn(3);
        $crudController->adminDomainFilterTabsFacade = $this->createStub(AdminDomainFilterTabsFacade::class);
        $crudController->adminDomainTabsFacade = $domainTabsFacadeStub;
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString('o.domainId IN (:listDomainFilterDomainIds)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame([3], $queryBuilder->getParameter('listDomainFilterDomainIds')->getValue());
    }

    public function testListDomainFilterExcludesEverythingWithoutAvailableDomains(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, [4]);
        $crudController = $this->createCrudController($crudConfig, [1, 2, 3], TestDomainSeparatedEntity::class);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(null);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString('1 = 0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testListDomainFilterSkipsEntityWithoutDomainSeparatedEntityInterface(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER);
        $crudController = $this->createCrudController($crudConfig);
        $crudController->adminDomainFilterTabsFacade = $this->createStub(AdminDomainFilterTabsFacade::class);
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertNull($queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testListDomainFilterSkipsListWithoutConfiguredControl(): void
    {
        $crudController = $this->createCrudController(new CrudConfig('Product review'), [1, 2, 3], TestDomainSeparatedEntity::class);
        $crudController->adminDomainFilterTabsFacade = $this->createStub(AdminDomainFilterTabsFacade::class);
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertNull($queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    public function testListDomainFilterAppliesConfiguredDomainIdField(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, domainIdField: 'relatedDomainId');
        $crudController = $this->createCrudController($crudConfig);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(2);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString('o.relatedDomainId IN (:listDomainFilterDomainIds)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame([2], $queryBuilder->getParameter('listDomainFilterDomainIds')->getValue());
    }

    public function testListDomainFilterAppliesConfiguredDomainIdFieldThroughToManyAssociation(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, domainIdField: 'domains.domainId');
        $crudController = $this->createCrudController($crudConfig);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(null);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilderWithAssociation(
            new OneToManyAssociationMapping('domains', stdClass::class, TestEntityDomain::class),
        );

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString(
            sprintf(
                'EXISTS (SELECT 1 FROM %s listDomainFilterRelation'
                    . ' WHERE listDomainFilterRelation MEMBER OF o.domains'
                    . ' AND listDomainFilterRelation.domainId IN (:listDomainFilterDomainIds))',
                TestEntityDomain::class,
            ),
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame([1, 2, 3], $queryBuilder->getParameter('listDomainFilterDomainIds')->getValue());
    }

    public function testListDomainFilterAppliesConfiguredDomainIdFieldThroughToOneAssociation(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, domainIdField: 'settings.domainId');
        $crudController = $this->createCrudController($crudConfig);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(3);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilderWithAssociation(
            new ManyToOneAssociationMapping('settings', stdClass::class, TestEntityDomain::class),
        );

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString(
            sprintf(
                'EXISTS (SELECT 1 FROM %s listDomainFilterRelation'
                    . ' WHERE o.settings = listDomainFilterRelation'
                    . ' AND listDomainFilterRelation.domainId IN (:listDomainFilterDomainIds))',
                TestEntityDomain::class,
            ),
            (string)$queryBuilder->getDQLPart('where'),
        );
        $this->assertSame([3], $queryBuilder->getParameter('listDomainFilterDomainIds')->getValue());
    }

    public function testListDomainFilterWithDomainIdFieldExcludesEverythingWithoutAvailableDomains(): void
    {
        $crudConfig = new CrudConfig('Product review');
        $crudConfig->setListDomainControl(CrudListDomainControl::QUICK_FILTER, [4], 'domains.domainId');
        $crudController = $this->createCrudController($crudConfig);
        $domainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $domainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(null);
        $crudController->adminDomainFilterTabsFacade = $domainFilterTabsFacadeStub;
        $crudController->adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $queryBuilder = $this->createQueryBuilder();

        $crudController->applyListDomainFilterForTest($queryBuilder);

        $this->assertStringContainsString('1 = 0', (string)$queryBuilder->getDQLPart('where'));
        $this->assertCount(0, $queryBuilder->getParameters());
    }

    /**
     * @param int[] $adminEnabledDomainIds
     * @param class-string $entityClass
     */
    private function createCrudController(
        CrudConfig $crudConfig,
        array $adminEnabledDomainIds = [1, 2, 3],
        string $entityClass = stdClass::class,
    ): TestCrudController {
        $crudController = new TestCrudController();
        $crudController->setDefinition(new Definition(
            TestCrudController::class,
            'TestCrudController',
            $entityClass,
            'Product review',
            $crudConfig->getConfig(),
            [],
            [],
        ));
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn($adminEnabledDomainIds);
        $crudController->domain = $domainStub;

        return $crudController;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = new QueryBuilder($this->createStub(EntityManagerInterface::class));
        $queryBuilder->select('o')->from(TestDomainSeparatedEntity::class, 'o');

        return $queryBuilder;
    }

    private function createQueryBuilderWithAssociation(AssociationMapping $associationMapping): QueryBuilder
    {
        $classMetadataStub = $this->createStub(ClassMetadata::class);
        $classMetadataStub->method('getAssociationMapping')->willReturn($associationMapping);
        $entityManagerStub = $this->createStub(EntityManagerInterface::class);
        $entityManagerStub->method('getClassMetadata')->willReturn($classMetadataStub);

        $queryBuilder = new QueryBuilder($entityManagerStub);
        $queryBuilder->select('o')->from(stdClass::class, 'o');

        return $queryBuilder;
    }
}

final class TestCrudController extends AbstractCrudController
{
    public function getSelectedListDomainIdForTest(): ?int
    {
        return $this->getSelectedListDomainId();
    }

    /**
     * @return int[]
     */
    public function getListDomainIdsForTest(): array
    {
        return $this->getListDomainIds();
    }

    public function applyListDomainFilterForTest(QueryBuilder $queryBuilder): void
    {
        $this->applyListDomainFilter($queryBuilder);
    }
}

final class TestDomainSeparatedEntity implements DomainSeparatedEntityInterface
{
    #[Override]
    public function getDomainId(): int
    {
        return 1;
    }
}

final class TestEntityDomain
{
}
