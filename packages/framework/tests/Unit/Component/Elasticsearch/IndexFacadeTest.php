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
     * @return \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition
     */
    private function getIndexDefinitionMock(): IndexDefinition
    {
        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->createMock(IndexDefinition::class);
        $indexDefinitionMock->method('getDomainId')->willReturn(1);
        return $indexDefinitionMock;
    }

    public function testCreateByIndexDefinitionCreatesIndexAndAlias(): void
    {
        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        $indexRepositoryMock->expects($this->once())->method('createIndex');
        $indexRepositoryMock->expects($this->once())->method('createAlias');

        /** @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactoryMock */
        $progressBarFactoryMock = $this->createMock(ProgressBarFactory::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacadeMock */
        $sqlLoggerFacadeMock = $this->createMock(SqlLoggerFacade::class);
        /** @var \Doctrine\ORM\EntityManager $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        $indexFacade = new IndexFacade($indexRepositoryMock, $progressBarFactoryMock, $sqlLoggerFacadeMock, $entityManagerMock);
        $indexFacade->createByIndexDefinition($this->getIndexDefinitionMock(), new NullOutput());
    }

    public function testMigrateByIndexDefinitionWhenMigrationIsNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        $indexRepositoryMock->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $indexRepositoryMock->expects($this->once())->method('createIndex');
        $indexRepositoryMock->expects($this->once())->method('reindex')->with($oldIndexName, $newIndexName);
        $indexRepositoryMock->expects($this->once())->method('createAlias');
        $indexRepositoryMock->expects($this->once())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactoryMock */
        $progressBarFactoryMock = $this->createMock(ProgressBarFactory::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacadeMock */
        $sqlLoggerFacadeMock = $this->createMock(SqlLoggerFacade::class);
        /** @var \Doctrine\ORM\EntityManager $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMock();
        $indexDefinitionMock->method('getVersionedIndexName')->willReturn($newIndexName);

        $indexFacade = new IndexFacade($indexRepositoryMock, $progressBarFactoryMock, $sqlLoggerFacadeMock, $entityManagerMock);
        $indexFacade->migrateByIndexDefinition($indexDefinitionMock, new NullOutput());
    }

    public function testMigrateByIndexDefinitionWhenMigrationIsNotNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        $indexRepositoryMock->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $indexRepositoryMock->expects($this->never())->method('createIndex');
        $indexRepositoryMock->expects($this->never())->method('reindex')->with($oldIndexName, $newIndexName);
        $indexRepositoryMock->expects($this->never())->method('createAlias');
        $indexRepositoryMock->expects($this->never())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactoryMock */
        $progressBarFactoryMock = $this->createMock(ProgressBarFactory::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacadeMock */
        $sqlLoggerFacadeMock = $this->createMock(SqlLoggerFacade::class);
        /** @var \Doctrine\ORM\EntityManager $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\MockObject $indexDefinitionMock */
        $indexDefinitionMock = $this->getIndexDefinitionMock();
        $indexDefinitionMock->method('getVersionedIndexName')->willReturn($oldIndexName);

        $indexFacade = new IndexFacade($indexRepositoryMock, $progressBarFactoryMock, $sqlLoggerFacadeMock, $entityManagerMock);
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
        $indexDefinitionMock = $this->createMock(IndexDefinition::class);
        $indexDefinitionMock->method('getIndexAlias')->willReturn($indexAlias);
        $indexDefinitionMock->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository|\PHPUnit\Framework\MockObject\MockObject $indexRepositoryMock */
        $indexRepositoryMock = $this->createMock(IndexRepository::class);
        if (empty($exportData)) {
            $indexRepositoryMock->expects($this->never())->method('bulkUpdate');
        } else {
            $indexRepositoryMock->expects($this->once())->method('bulkUpdate')->with($indexAlias, $exportData);
        }

        if (empty($expectedIdsToDelete)) {
            $indexRepositoryMock->expects($this->never())->method('deleteIds');
        } else {
            $indexRepositoryMock->expects($this->once())->method('deleteIds')->with($indexAlias, $expectedIdsToDelete);
        }

        /** @var \Shopsys\FrameworkBundle\Component\Console\ProgressBarFactory $progressBarFactoryMock */
        $progressBarFactoryMock = $this->createMock(ProgressBarFactory::class);
        /** @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacadeMock */
        $sqlLoggerFacadeMock = $this->createMock(SqlLoggerFacade::class);
        /** @var \Doctrine\ORM\EntityManager $entityManagerMock */
        $entityManagerMock = $this->createMock(EntityManager::class);


        $indexFacade = new IndexFacade($indexRepositoryMock, $progressBarFactoryMock, $sqlLoggerFacadeMock, $entityManagerMock);
        $indexFacade->exportIds($indexMock, $indexDefinitionMock, $affectedIds);
    }

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
            ]
        ];
    }
}
