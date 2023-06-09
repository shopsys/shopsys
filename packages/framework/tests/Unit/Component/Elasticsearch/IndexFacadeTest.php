<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\MockObject\MockObject;
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
    private IndexRepository|MockObject $indexRepositoryMock;

    private ProgressBarFactory $progressBarFactoryMock;

    private SqlLoggerFacade $sqlLoggerFacadeMock;

    private EntityManager $entityManagerMock;

    protected function setUp(): void
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
            $this->entityManagerMock,
        );
    }

    public function testMigrateWhenMigrationIsNecessary(): void
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
        $indexFacade->migrate($indexDefinitionMock, new NullOutput());
    }

    public function testMigrateWhenMigrationIsNotNecessary(): void
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
        $indexFacade->migrate($indexDefinitionMock, new NullOutput());
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
        $indexMock->method('getExportDataForIds')->with(Domain::FIRST_DOMAIN_ID, $affectedIds)->willReturn(
            $exportData,
        );
        $indexMock->method('getExportBatchSize')->willReturn(100);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMockReturningDomainId();
        $indexDefinitionMock->method('getIndexAlias')->willReturn($indexAlias);

        if (count($exportData) === 0) {
            $this->indexRepositoryMock->expects($this->never())->method('bulkUpdate');
        } else {
            $this->indexRepositoryMock->expects($this->once())->method('bulkUpdate')->with($indexAlias, $exportData);
        }

        if (count($expectedIdsToDelete) === 0) {
            $this->indexRepositoryMock->expects($this->never())->method('deleteIds');
        } else {
            $this->indexRepositoryMock->expects($this->once())->method('deleteIds')->with(
                $indexAlias,
                $expectedIdsToDelete,
            );
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
