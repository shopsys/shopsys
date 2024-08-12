<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

abstract class AbstractFileFixture extends AbstractReferenceFixture
{
    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly FilesystemOperator $filesystem,
        protected readonly Filesystem $localFilesystem,
        protected readonly MountManager $mountManager,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param string[] $sequences
     * @throws \Doctrine\DBAL\Exception
     */
    protected function syncDatabaseSequences(array $sequences): void
    {
        foreach ($sequences as $sequence) {
            $parts = explode('.', $sequence);
            list($table, $column) = $parts;

            $this->em->getConnection()->executeStatement(
                sprintf(
                    'SELECT SETVAL(pg_get_serial_sequence(\'%s\', \'%s\'), COALESCE((SELECT MAX(%s) FROM %s) + 1, 1), false)',
                    $table,
                    $column,
                    $column,
                    $table,
                ),
            );
        }
    }

    /**
     * @param string[] $tables
     * @throws \Doctrine\DBAL\Exception
     */
    protected function truncateDatabaseTables(array $tables): void
    {
        if (count($tables) <= 0) {
            return;
        }

        $this->em->getConnection()->executeStatement(
            sprintf('TRUNCATE TABLE %s', implode(', ', $tables)),
        );
    }

    /**
     * @param string $origin
     * @param string $target
     */
    protected function moveFilesFromLocalFilesystemToFilesystem(string $origin, string $target): void
    {
        $finder = new Finder();
        $finder->files()->in($origin);

        foreach ($finder as $file) {
            $filepath = TransformString::removeDriveLetterFromPath($file->getPathname());

            if (!$this->localFilesystem->exists($filepath)) {
                continue;
            }

            $newFilepath = $target . $file->getRelativePathname();

            if ($this->filesystem->has($newFilepath)) {
                $this->filesystem->delete($newFilepath);
            }
            $this->mountManager->copy('local://' . $filepath, 'main://' . $newFilepath);
        }
    }
}
