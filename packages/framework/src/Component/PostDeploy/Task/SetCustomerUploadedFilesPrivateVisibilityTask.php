<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Override;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetCustomerUploadedFilesPrivateVisibilityTask implements PostDeployTaskInterface
{
    protected const int BATCH_SIZE = 100;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly FilesystemOperator $filesystem,
        protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
    ) {
    }

    #[Override]
    public function run(SymfonyStyle $style): void
    {
        $style->section('Customer uploaded files visibility');

        $processedCount = $this->setCustomerUploadedFilesPrivateVisibility($style);

        if ($processedCount === 0) {
            $style->success('No customer uploaded files found.');

            return;
        }

        $style->success(sprintf('Set private visibility for %d customer uploaded files.', $processedCount));
    }

    protected function setCustomerUploadedFilesPrivateVisibility(SymfonyStyle $style): int
    {
        $tableName = $this->getCustomerUploadedFilesTableName();
        $totalCount = (int)$this->entityManager->getConnection()->fetchOne(
            sprintf('SELECT COUNT(id) FROM %s', $tableName),
        );

        if ($totalCount === 0) {
            return 0;
        }

        $style->progressStart($totalCount);

        $processedCount = 0;
        $lastProcessedId = 0;

        while (true) {
            $rows = $this->getCustomerUploadedFileRows($tableName, $lastProcessedId);

            if ($rows === []) {
                break;
            }

            foreach ($rows as $row) {
                $this->filesystem->setVisibility(
                    $this->getCustomerUploadedFilepath($row),
                    Visibility::PRIVATE,
                );

                $lastProcessedId = (int)$row['id'];
                $processedCount++;
                $style->progressAdvance();
            }
        }

        $style->progressFinish();

        return $processedCount;
    }

    protected function getCustomerUploadedFilesTableName(): string
    {
        return $this->entityManager->getClassMetadata(CustomerUploadedFile::class)->getTableName();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getCustomerUploadedFileRows(string $tableName, int $lastProcessedId): array
    {
        return $this->entityManager->getConnection()->fetchAllAssociative(
            sprintf(
                'SELECT id, entity_name, extension FROM %s WHERE id > :lastProcessedId ORDER BY id ASC LIMIT %d',
                $tableName,
                static::BATCH_SIZE,
            ),
            ['lastProcessedId' => $lastProcessedId],
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    protected function getCustomerUploadedFilepath(array $row): string
    {
        return $this->customerUploadedFileLocator->getAbsoluteFilePath(
            sprintf('%s/%d.%s', (string)$row['entity_name'], (int)$row['id'], (string)$row['extension']),
        );
    }
}
