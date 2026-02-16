<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Elasticsearch;

use Doctrine\ORM\EntityManager;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
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

    private ProgressBarFactory $progressBarFactoryStub;

    private SqlLoggerFacade $sqlLoggerFacadeStub;

    private EntityManager $entityManagerStub;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->indexRepositoryMock = $this->createMock(IndexRepository::class);
        $this->progressBarFactoryStub = $this->createStub(ProgressBarFactory::class);
        $this->sqlLoggerFacadeStub = $this->createStub(SqlLoggerFacade::class);
        $this->entityManagerStub = $this->createStub(EntityManager::class);
    }

    private function getIndexDefinitionStubReturningDomainId(): IndexDefinition
    {
        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\Stub $indexDefinitionStub */
        $indexDefinitionStub = $this->createStub(IndexDefinition::class);
        $indexDefinitionStub->method('getDomainId')->willReturn(1);

        return $indexDefinitionStub;
    }

    private function createIndexFacadeInstance(): IndexFacade
    {
        return new IndexFacade(
            $this->indexRepositoryMock,
            $this->progressBarFactoryStub,
            $this->sqlLoggerFacadeStub,
            $this->entityManagerStub,
        );
    }

    public function testMigrateWhenMigrationIsNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        $this->indexRepositoryMock->expects($this->any())->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $this->indexRepositoryMock->expects($this->once())->method('createIndex');
        $this->indexRepositoryMock->expects($this->once())->method('reindex')->with($oldIndexName, $newIndexName);
        $this->indexRepositoryMock->expects($this->once())->method('createAlias');
        $this->indexRepositoryMock->expects($this->once())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\Stub $indexDefinitionStub */
        $indexDefinitionStub = $this->getIndexDefinitionStubReturningDomainId();
        $indexDefinitionStub->method('getVersionedIndexName')->willReturn($newIndexName);

        $indexFacade = $this->createIndexFacadeInstance();
        $indexFacade->migrate($indexDefinitionStub, new NullOutput());
    }

    public function testMigrateWhenMigrationIsNotNecessary(): void
    {
        $oldIndexName = 'index_old';
        $newIndexName = 'index_new';

        $this->indexRepositoryMock->expects($this->any())->method('findCurrentIndexNameForAlias')->willReturn($oldIndexName);
        $this->indexRepositoryMock->expects($this->never())->method('createIndex');
        $this->indexRepositoryMock->expects($this->never())->method('reindex')->with($oldIndexName, $newIndexName);
        $this->indexRepositoryMock->expects($this->never())->method('createAlias');
        $this->indexRepositoryMock->expects($this->never())->method('deleteIndex')->with($oldIndexName);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\Stub $indexDefinitionStub */
        $indexDefinitionStub = $this->getIndexDefinitionStubReturningDomainId();
        $indexDefinitionStub->method('getVersionedIndexName')->willReturn($oldIndexName);

        $indexFacade = $this->createIndexFacadeInstance();
        $indexFacade->migrate($indexDefinitionStub, new NullOutput());
    }

    #[DataProvider('exportIdsDataProvider')]
    public function testExportIds(array $affectedIds, array $exportData, array $expectedIdsToDelete): void
    {
        $indexAlias = 'mock_alias_1';

        /** @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex|\PHPUnit\Framework\MockObject\MockObject $indexMock */
        $indexMock = $this->createMock(ProductIndex::class);
        $indexMock->expects($this->any())->method('getExportDataForIds')->with(Domain::FIRST_DOMAIN_ID, $affectedIds)->willReturn(
            $exportData,
        );
        $indexMock->expects($this->any())->method('getExportBatchSize')->willReturn(100);

        /** @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinition|\PHPUnit\Framework\MockObject\Stub $indexDefinitionStub */
        $indexDefinitionStub = $this->getIndexDefinitionStubReturningDomainId();
        $indexDefinitionStub->method('getIndexAlias')->willReturn($indexAlias);

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
        $indexFacade->exportIds($indexMock, $indexDefinitionStub, $affectedIds);
    }

    public static function exportIdsDataProvider(): array
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
