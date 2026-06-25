<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Crud\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterMode;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterType;
use Shopsys\AdministrationBundle\Component\Crud\Domain\CrudDomainFilterResolver;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CrudDomainFilterResolverTest extends TestCase
{
    private const string ENTITY_CLASS = Order::class;

    public function testResolveTypeReturnsNoneWhenDisabled(): void
    {
        $resolver = $this->createResolver($this->createManagerRegistryStub());
        $config = (new CrudConfig('test'))->disableDomainFilter()->getConfig();

        $this->assertSame(DomainFilterType::NONE, $resolver->resolveType(self::ENTITY_CLASS, $config));
    }

    public function testResolveTypeReturnsExplicitlyConfiguredTypeWithoutTouchingMetadata(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->never())->method('getManagerForClass');

        $resolver = $this->createResolver($managerRegistry);
        $config = (new CrudConfig('test'))->configureDomainFilter(DomainFilterType::COLLECTION)->getConfig();

        $this->assertSame(DomainFilterType::COLLECTION, $resolver->resolveType(self::ENTITY_CLASS, $config));
    }

    public function testResolveTypeAutodetectsScalar(): void
    {
        $classMetadata = $this->createStub(ClassMetadata::class);
        $classMetadata->method('hasField')->willReturn(true);

        $resolver = $this->createResolver($this->createManagerRegistryStub($classMetadata));
        $config = (new CrudConfig('test'))->getConfig();

        $this->assertSame(DomainFilterType::SCALAR, $resolver->resolveType(self::ENTITY_CLASS, $config));
    }

    public function testResolveTypeAutodetectsCollection(): void
    {
        $targetClassMetadata = $this->createStub(ClassMetadata::class);
        $targetClassMetadata->method('hasField')->willReturn(true);

        $classMetadata = $this->createStub(ClassMetadata::class);
        $classMetadata->method('hasField')->willReturn(false);
        $classMetadata->method('hasAssociation')->willReturn(true);
        $classMetadata->method('getAssociationTargetClass')->willReturn('App\\Model\\TestEntityDomain');

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getClassMetadata')->willReturnMap([
            [self::ENTITY_CLASS, $classMetadata],
            ['App\\Model\\TestEntityDomain', $targetClassMetadata],
        ]);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $resolver = $this->createResolver($managerRegistry);
        $config = (new CrudConfig('test'))->getConfig();

        $this->assertSame(DomainFilterType::COLLECTION, $resolver->resolveType(self::ENTITY_CLASS, $config));
    }

    public function testResolveTypeAutodetectsNoneForNonDomainEntity(): void
    {
        $classMetadata = $this->createStub(ClassMetadata::class);
        $classMetadata->method('hasField')->willReturn(false);
        $classMetadata->method('hasAssociation')->willReturn(false);

        $resolver = $this->createResolver($this->createManagerRegistryStub($classMetadata));
        $config = (new CrudConfig('test'))->getConfig();

        $this->assertSame(DomainFilterType::NONE, $resolver->resolveType(self::ENTITY_CLASS, $config));
    }

    public function testGetSelectedDomainIdUsesAdminDomainTabsFacadeInSwitchMode(): void
    {
        $adminDomainTabsFacade = $this->createStub(AdminDomainTabsFacade::class);
        $adminDomainTabsFacade->method('getSelectedDomainId')->willReturn(2);

        $adminDomainFilterTabsFacade = $this->createMock(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacade->expects($this->never())->method('getSelectedDomainId');

        $resolver = new CrudDomainFilterResolver(
            $this->createStub(ManagerRegistry::class),
            $adminDomainTabsFacade,
            $adminDomainFilterTabsFacade,
        );

        $this->assertSame(2, $resolver->getSelectedDomainId('TestController', DomainFilterMode::SWITCH));
    }

    public function testGetSelectedDomainIdUsesAdminDomainFilterTabsFacadeInFilterMode(): void
    {
        $adminDomainTabsFacade = $this->createMock(AdminDomainTabsFacade::class);
        $adminDomainTabsFacade->expects($this->never())->method('getSelectedDomainId');

        $adminDomainFilterTabsFacade = $this->createStub(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacade->method('getSelectedDomainId')->willReturn(null);

        $resolver = new CrudDomainFilterResolver(
            $this->createStub(ManagerRegistry::class),
            $adminDomainTabsFacade,
            $adminDomainFilterTabsFacade,
        );

        $this->assertNull($resolver->getSelectedDomainId('TestController', DomainFilterMode::FILTER));
    }

    public function testApplyFilterDoesNothingForNoneType(): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $originalDql = $queryBuilder->getDQL();

        $this->createResolver($this->createManagerRegistryStub())
            ->applyFilter($queryBuilder, DomainFilterType::NONE, 1, (new CrudConfig('test'))->getConfig());

        $this->assertSame($originalDql, $queryBuilder->getDQL());
    }

    public function testApplyFilterDoesNothingForNullDomainId(): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $originalDql = $queryBuilder->getDQL();

        $this->createResolver($this->createManagerRegistryStub())
            ->applyFilter($queryBuilder, DomainFilterType::SCALAR, null, (new CrudConfig('test'))->getConfig());

        $this->assertSame($originalDql, $queryBuilder->getDQL());
    }

    public function testApplyFilterAddsScalarCondition(): void
    {
        $queryBuilder = $this->createQueryBuilder();

        $this->createResolver($this->createManagerRegistryStub())
            ->applyFilter($queryBuilder, DomainFilterType::SCALAR, 3, (new CrudConfig('test'))->getConfig());

        $this->assertStringContainsString('o.domainId = :crudDomainFilterId', $queryBuilder->getDQL());
        $this->assertSame(3, $queryBuilder->getParameter('crudDomainFilterId')->getValue());
    }

    public function testApplyFilterJoinsSelectedDomainRowForCollection(): void
    {
        $queryBuilder = $this->createQueryBuilder();

        $this->createResolver($this->createManagerRegistryStub())
            ->applyFilter($queryBuilder, DomainFilterType::COLLECTION, 4, (new CrudConfig('test'))->getConfig());

        $dql = $queryBuilder->getDQL();
        // COLLECTION must NOT filter rows out, only LEFT JOIN the selected domain's row
        $this->assertStringNotContainsString('EXISTS', $dql);
        $this->assertStringContainsString('LEFT JOIN o.domains crudDomainFilterDomain', $dql);
        $this->assertStringContainsString('crudDomainFilterDomain.domainId = :crudDomainFilterId', $dql);
        $this->assertSame(4, $queryBuilder->getParameter('crudDomainFilterId')->getValue());
    }

    private function createResolver(ManagerRegistry $managerRegistry): CrudDomainFilterResolver
    {
        return new CrudDomainFilterResolver(
            $managerRegistry,
            $this->createStub(AdminDomainTabsFacade::class),
            $this->createStub(AdminDomainFilterTabsFacade::class),
        );
    }

    private function createManagerRegistryStub(?ClassMetadata $classMetadata = null): ManagerRegistry
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        if ($classMetadata !== null) {
            $entityManager->method('getClassMetadata')->willReturn($classMetadata);
        }

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        return $managerRegistry;
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $queryBuilder = new QueryBuilder($this->createStub(EntityManagerInterface::class));

        return $queryBuilder->select('o')->from(self::ENTITY_CLASS, 'o');
    }
}
