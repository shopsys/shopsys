<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\PostDeploy\Task;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Nette\Utils\Strings;
use Override;
use Symfony\Component\Console\Style\SymfonyStyle;

class WebalizeUploadedFileNamesTask implements PostDeployTaskInterface
{
    protected const int BATCH_SIZE = 10000;

    public function __construct(
        protected readonly Connection $connection,
    ) {
    }

    #[Override]
    public function run(SymfonyStyle $style): void
    {
        $totalCount = 0;

        foreach (['uploaded_files', 'customer_uploaded_files'] as $tableName) {
            $style->section($tableName);
            $totalCount += $this->webalizeNamesInTable($tableName, $style);
        }

        if ($totalCount === 0) {
            $style->success('All uploaded file names are already webalized.');

            return;
        }

        $style->success(sprintf('Webalized names of %d uploaded files.', $totalCount));
    }

    protected function webalizeNamesInTable(string $tableName, SymfonyStyle $style): int
    {
        $lastId = 0;
        $processedCount = 0;

        do {
            $rows = $this->connection->fetchAllAssociative(
                sprintf(
                    "SELECT id, name FROM %s WHERE id > :lastId AND name !~ '^[A-Za-z0-9-]+$' ORDER BY id LIMIT :limit",
                    $tableName,
                ),
                ['lastId' => $lastId, 'limit' => static::BATCH_SIZE],
                ['lastId' => ParameterType::INTEGER, 'limit' => ParameterType::INTEGER],
            );

            if ($rows === []) {
                break;
            }

            $sqlValues = [];
            $parameters = [];

            foreach ($rows as $index => $row) {
                $lastId = $row['id'];
                $webalizedName = Strings::webalize($row['name'], lower: false);

                if ($webalizedName === '') {
                    $webalizedName = 'file';
                }

                $sqlValues[] = sprintf('(:id%1$d::int, :name%1$d)', $index);
                $parameters['id' . $index] = $row['id'];
                $parameters['name' . $index] = $webalizedName;
            }

            $this->connection->executeStatement(
                sprintf(
                    'UPDATE %s AS t SET name = v.name FROM (VALUES %s) AS v(id, name) WHERE t.id = v.id',
                    $tableName,
                    implode(', ', $sqlValues),
                ),
                $parameters,
            );

            $rowsCount = count($rows);
            $processedCount += $rowsCount;
            $style->writeln(sprintf('Processed %d rows...', $processedCount));
        } while ($rowsCount === static::BATCH_SIZE);

        if ($processedCount === 0) {
            $style->info('No file names to webalize.');
        }

        return $processedCount;
    }
}
