<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Override;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileLocator;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'shopsys:recalculate-file-sizes',
    description: 'Populates file size for uploaded files and images that do not have it stored.',
)]
class RecalculateFileSizesCommand extends Command
{
    protected const int BATCH_SIZE = 100;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly FilesystemOperator $filesystem,
        protected readonly UploadedFileLocator $uploadedFileLocator,
        protected readonly CustomerUploadedFileLocator $customerUploadedFileLocator,
        protected readonly ImageLocator $imageLocator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $uploadedFilesCount = $this->recalculateUploadedFiles($style);
        $customerFilesCount = $this->recalculateCustomerUploadedFiles($style);
        $imagesCount = $this->recalculateImages($style);

        $totalCount = $uploadedFilesCount + $customerFilesCount + $imagesCount;

        if ($totalCount === 0) {
            $style->success('All files already have sizes stored.');
        } else {
            $style->success(sprintf(
                'Recalculated sizes for %d files (%d uploaded files, %d customer files, %d images).',
                $totalCount,
                $uploadedFilesCount,
                $customerFilesCount,
                $imagesCount,
            ));
        }

        return Command::SUCCESS;
    }

    protected function recalculateUploadedFiles(SymfonyStyle $style): int
    {
        $style->section('Uploaded files');

        return $this->processEntities(
            $style,
            UploadedFile::class,
            fn (array $row) => $this->uploadedFileLocator->getAbsoluteFilePath(
                sprintf('%d.%s', $row['id'], $row['extension']),
            ),
        );
    }

    protected function recalculateCustomerUploadedFiles(SymfonyStyle $style): int
    {
        $style->section('Customer uploaded files');

        return $this->processEntities(
            $style,
            CustomerUploadedFile::class,
            fn (array $row) => $this->customerUploadedFileLocator->getAbsoluteFilePath(
                sprintf('%s/%d.%s', $row['entityName'], $row['id'], $row['extension']),
            ),
        );
    }

    protected function recalculateImages(SymfonyStyle $style): int
    {
        $style->section('Images');

        return $this->processEntities(
            $style,
            Image::class,
            fn (array $row) => $this->imageLocator->getAbsoluteImageFilepathFromAttributes(
                $row['id'],
                $row['extension'],
                $row['entityName'],
                $row['type'],
            ),
        );
    }

    /**
     * @param class-string $entityClass
     * @param callable(array<string, mixed>): string $filepathResolver
     */
    protected function processEntities(
        SymfonyStyle $style,
        string $entityClass,
        callable $filepathResolver,
    ): int {
        $totalCount = (int)$this->entityManager->createQueryBuilder()
            ->select('COUNT(e.id)')
            ->from($entityClass, 'e')
            ->where('e.filesize IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        if ($totalCount === 0) {
            $style->info('No files to process.');

            return 0;
        }

        $tableName = $this->entityManager->getClassMetadata($entityClass)->getTableName();
        $style->progressStart($totalCount);
        $processedCount = 0;

        while (true) {
            $rows = $this->entityManager->createQueryBuilder()
                ->select('e')
                ->from($entityClass, 'e')
                ->where('e.filesize IS NULL')
                ->setMaxResults(static::BATCH_SIZE)
                ->getQuery()
                ->getArrayResult();

            if (count($rows) === 0) {
                break;
            }

            foreach ($rows as $row) {
                try {
                    $filepath = $filepathResolver($row);
                    $filesize = $this->filesystem->fileSize($filepath);
                } catch (FilesystemException) {
                    $filesize = 0;
                }

                $this->entityManager->getConnection()->executeStatement(
                    sprintf('UPDATE %s SET filesize = :filesize WHERE id = :id', $tableName),
                    [
                        'filesize' => $filesize,
                        'id' => $row['id'],
                    ],
                );

                $processedCount++;
                $style->progressAdvance();
            }
        }

        $style->progressFinish();

        return $processedCount;
    }
}
