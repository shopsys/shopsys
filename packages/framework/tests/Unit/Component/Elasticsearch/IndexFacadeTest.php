<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Symfony\Component\Console\Output\NullOutput;

class IndexFacadeTest extends TestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $indexRepositoryMock;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory
     */
    private $progressBarFactoryMock;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    private $sqlLoggerFacadeMock;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManagerMock;

    public function setUp()
    {
        parent::setUp();

        $this->indexRepositoryMock = $this->createMock(IndexRepository::class);
        $this->progressBarFactoryMock = $this->createMock(ProgressBarFactory::class);
        $this->sqlLoggerFacadeMock = $this->createMock(SqlLoggerFacade::class);
        $this->entityManagerMock = $this->createMock(EntityManager::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    private function getIndexDefinitionMockReturningDomainId(): IndexDefinition
    {
        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->createMock(IndexDefinition::class);
        $indexDefinitionMock->method('getDomainId')->willReturn(1);
        return $indexDefinitionMock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade
     */
    private function createIndexFacadeInstance(): IndexFacade
    {
        return new IndexFacade(
            $this->indexRepositoryMock,
            $this->progressBarFactoryMock,
            $this->sqlLoggerFacadeMock,
            $this->entityManagerMock
        );
    }

    public function testCreateByIndexDefinitionCreatesIndexAndAlias(): void
    {
        $this->indexRepositoryMock->expects($this->once())->method('createIndex');
        $this->indexRepositoryMock->expects($this->once())->method('createAlias');

        $indexFacade = $this->createIndexFacadeInstance();
        $indexFacade->createByIndexDefinition($this->getIndexDefinitionMockReturningDomainId(), new NullOutput());
    }

    public function testMigrateByIndexDefinitionWhenMigrationIsNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        $this->indexRepositoryMock->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $this->indexRepositoryMock->expects($this->once())->method('createIndex');
        $this->indexRepositoryMock->expects($this->once())->method('reindex')->with($oldIndexName, $newIndexName);
        $this->indexRepositoryMock->expects($this->once())->method('createAlias');
        $this->indexRepositoryMock->expects($this->once())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMockReturningDomainId();
        $indexDefinitionMock->method('getVersionedIndexName')->willReturn($newIndexName);

        $indexFacade = $this->createIndexFacadeInstance();
        $indexFacade->migrateByIndexDefinition($indexDefinitionMock, new NullOutput());
    }

    public function testMigrateByIndexDefinitionWhenMigrationIsNotNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        $this->indexRepositoryMock->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $this->indexRepositoryMock->expects($this->never())->method('createIndex');
        $this->indexRepositoryMock->expects($this->never())->method('reindex')->with($oldIndexName, $newIndexName);
        $this->indexRepositoryMock->expects($this->never())->method('createAlias');
        $this->indexRepositoryMock->expects($this->never())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMockReturningDomainId();
        $indexDefinitionMock->method('getVersionedIndexName')->willReturn($oldIndexName);

        $indexFacade = $this->createIndexFacadeInstance();
        $indexFacade->migrateByIndexDefinition($indexDefinitionMock, new NullOutput());
    }

    /**
     * @param array $affectedIds
     * @param array $exportData
     * @param array $expectedIdsToDelete
     * @dataProvider exportIdsDataProvider
     */
    public function testExportIds(array $affectedIds, array $exportData, array $expectedIdsToDelete): void
    {
        $indexAlias = 'mock_alias_1';

        /** @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex|\PHPUnit\Framework\MockObject\MockObject $indexMock */
        $indexMock = $this->createMock(ProductIndex::class);
        $indexMock->method('getExportDataForIds')->with(Domain::FIRST_DOMAIN_ID, $affectedIds)->willReturn($exportData);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMockReturningDomainId();
        $indexDefinitionMock->method('getIndexAlias')->willReturn($indexAlias);

        if (empty($exportData)) {
            $this->indexRepositoryMock->expects($this->never())->method('bulkUpdate');
        } else {
            $this->indexRepositoryMock->expects($this->once())->method('bulkUpdate')->with($indexAlias, $exportData);
        }

        if (empty($expectedIdsToDelete)) {
            $this->indexRepositoryMock->expects($this->never())->method('deleteIds');
        } else {
            $this->indexRepositoryMock->expects($this->once())->method('deleteIds')->with($indexAlias, $expectedIdsToDelete);
        }

        $indexFacade = $this->createIndexFacadeInstance();
        $indexFacade->exportIds($indexMock, $indexDefinitionMock, $affectedIds);
    }

    /**
     * @return array
     */
    public function exportIdsDataProvider(): array
    {
        return [
            [
                [1, 2, 3],
                [
                    1 => ['foo' => 'bar'],
                    3 => ['foo' => 'baz'],
                ],
                [2],
            ],
            [
                [1, 2, 3],
                [],
                [1, 2, 3],
            ],
            [
                [1, 2, 3],
                [
                    1 => ['foo' => 'bar'],
                    2 => ['foo' => 'bam'],
                    3 => ['foo' => 'baz'],
                ],
                [],
            ],
        ];
    }
}
