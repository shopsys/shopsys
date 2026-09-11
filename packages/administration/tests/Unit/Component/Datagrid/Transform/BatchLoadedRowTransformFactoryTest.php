<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Transform;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use LogicException;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;
use Shopsys\AdministrationBundle\Component\Datagrid\Transform\BatchLoadedRowTransformFactory;
use stdClass;

final class BatchLoadedRowTransformFactoryTest extends TestCase
{
    public function testIndexedLoaderIsCalledOnceForAllRowsOfThePage(): void
    {
        $loaderCalls = [];
        $transform = $this->createFactory()->createFromIndexedLoader(static function (array $rowIds) use (&$loaderCalls): array {
            $loaderCalls[] = $rowIds;

            return [1 => 'first', 2 => 'second'];
        });
        $results = [['id' => 1], ['id' => '2']];

        $this->assertSame('first', $transform(null, $results[0], $results));
        $this->assertSame('second', $transform(null, $results[1], $results));
        $this->assertSame([[1, 2]], $loaderCalls);
    }

    public function testRowTheLoaderReturnsNothingForGetsNull(): void
    {
        $loaderCallCount = 0;
        $transform = $this->createFactory()->createFromIndexedLoader(static function (array $rowIds) use (&$loaderCallCount): array {
            $loaderCallCount++;

            return [1 => 'first'];
        });
        $results = [['id' => 1], ['id' => 2]];

        $this->assertSame('first', $transform(null, $results[0], $results));
        $this->assertNull($transform(null, $results[1], $results));
        $this->assertSame(1, $loaderCallCount);
    }

    public function testCustomRowIdentifierIsUsed(): void
    {
        $transform = $this->createFactory()->createFromIndexedLoader(
            static fn (array $rowIds): array => array_combine($rowIds, array_map(static fn (int $rowId): string => 'value ' . $rowId, $rowIds)),
            'productId',
        );
        $results = [['productId' => 7], ['productId' => 9]];

        $this->assertSame('value 9', $transform(null, $results[1], $results));
    }

    public function testRowWithoutTheIdentifierFails(): void
    {
        $transform = $this->createFactory()->createFromIndexedLoader(static fn (array $rowIds): array => []);
        $results = [['productId' => 7]];

        $this->expectException(LogicException::class);
        $transform(null, $results[0], $results);
    }

    public function testEntitiesLoaderIndexesTheEntitiesByTheirIdentifierAndMapsThem(): void
    {
        $firstEntity = $this->createEntity(1, 'first');
        $secondEntity = $this->createEntity(2, 'second');
        $transform = $this->createFactory()->createFromEntitiesLoader(
            static fn (array $rowIds): array => [$secondEntity, $firstEntity],
            static fn (stdClass $entity): string => strtoupper($entity->name),
        );
        $results = [['id' => 1], ['id' => 2]];

        $this->assertSame('FIRST', $transform(null, $results[0], $results));
        $this->assertSame('SECOND', $transform(null, $results[1], $results));
    }

    public function testEntitiesLoaderReturnsTheEntityItselfWithoutMapping(): void
    {
        $entity = $this->createEntity(5, 'only');
        $transform = $this->createFactory()->createFromEntitiesLoader(static fn (array $rowIds): array => [$entity]);
        $results = [['id' => 5]];

        $this->assertSame($entity, $transform(null, $results[0], $results));
    }

    private function createEntity(int $id, string $name): stdClass
    {
        $entity = new stdClass();
        $entity->id = $id;
        $entity->name = $name;

        return $entity;
    }

    private function createFactory(): BatchLoadedRowTransformFactory
    {
        $classMetadataStub = $this->createStub(ClassMetadata::class);
        $classMetadataStub->method('getIdentifierFieldNames')->willReturn(['id']);
        $classMetadataStub->method('getIdentifierValues')->willReturnCallback(static fn (object $entity): array => ['id' => $entity->id]);
        $objectManagerStub = $this->createStub(ObjectManager::class);
        $objectManagerStub->method('getClassMetadata')->willReturn($classMetadataStub);
        $managerRegistryStub = $this->createStub(ManagerRegistry::class);
        $managerRegistryStub->method('getManagerForClass')->willReturn($objectManagerStub);

        return new BatchLoadedRowTransformFactory(new CrudEntityIdentifierExtractor($managerRegistryStub));
    }
}
