<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Symfony\Component\Filesystem\Filesystem;

class UploadedFileDataFixture extends AbstractFileFixture implements DependentFixtureInterface
{
    public const UPLOADED_FILES_TABLE_NAME = 'uploaded_files';
    public const UPLOADED_FILES_TRANSLATIONS_TABLE_NAME = 'uploaded_files_translations';
    public const UPLOADED_FILES_RELATIONS_TABLE_NAME = 'uploaded_files_relations';
    public const SEQUENCES_TO_SYNC = ['uploaded_files.id'];

    public const array TABLES_TO_TRUNCATE = [
        self::UPLOADED_FILES_TABLE_NAME,
        self::UPLOADED_FILES_TRANSLATIONS_TABLE_NAME,
        self::UPLOADED_FILES_RELATIONS_TABLE_NAME,
    ];

    /**
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param string $dataFixturesFilesDirectory
     * @param string $targetFilesDirectory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        FilesystemOperator $filesystem,
        Filesystem $localFilesystem,
        MountManager $mountManager,
        EntityManagerInterface $em,
        private readonly string $dataFixturesFilesDirectory,
        private readonly string $targetFilesDirectory,
        private readonly Domain $domain,
    ) {
        parent::__construct($filesystem, $localFilesystem, $mountManager, $em);
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->truncateDatabaseTables(self::TABLES_TO_TRUNCATE);

        if (!file_exists($this->dataFixturesFilesDirectory)) {
            return;
        }

        $this->moveFilesFromLocalFilesystemToFilesystem(
            $this->dataFixturesFilesDirectory,
            $this->targetFilesDirectory,
        );

        $this->processProductsFiles();

        $this->syncDatabaseSequences(self::SEQUENCES_TO_SYNC);
    }

    private function processProductsFiles()
    {
        $specificProductsIdsIndexedByFilesIds = [
            1 => 1,
        ];

        $positions = [];

        foreach ($specificProductsIdsIndexedByFilesIds as $fileId => $productId) {
            $names = [];

            foreach ($this->domain->getAllLocales() as $locale) {
                $names[$locale] = 'Product ' . $productId . ' file';
            }

            $positions[$productId] = array_key_exists($productId, $positions) ? ++$positions[$productId] : 0;
            $this->saveFileIntoDb($productId, 'product', UploadedFileTypeConfig::DEFAULT_TYPE_NAME, $fileId, $names, $positions[$productId]);
        }
    }

    /**
     * @param int $entityId
     * @param string $entityName
     * @param string $type
     * @param int $fileId
     * @param array $names
     * @param int|null $position
     */
    private function saveFileIntoDb(
        int $entityId,
        string $entityName,
        string $type,
        int $fileId,
        array $names = [],
        ?int $position = null,
    ) {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO uploaded_files (id, entity_name, entity_id, type, name, slug, extension, position, modified_at)
            VALUES (:id, :entity_name, :entity_id, :type, :name, :slug, :extension, :position, :modified_at)',
            [
                'id' => $fileId,
                'entity_name' => $entityName,
                'entity_id' => $entityId,
                'type' => $type,
                'name' => sprintf('Product %d file', $entityId),
                'slug' => sprintf('product-%d-file', $entityId),
                'extension' => 'pdf',
                'position' => $position,
                'modified_at' => new DateTimeImmutable('2015-04-16 11:36:06'),
            ],
            [
                'id' => Types::INTEGER,
                'entity_name' => Types::STRING,
                'entity_id' => Types::INTEGER,
                'type' => Types::STRING,
                'name' => Types::STRING,
                'slug' => Types::STRING,
                'extension' => Types::STRING,
                'position' => Types::INTEGER,
                'modified_at' => Types::DATETIME_IMMUTABLE,
            ],
        );

        foreach ($this->domain->getAllLocales() as $locale) {
            $this->em->getConnection()->executeStatement(
                'INSERT INTO uploaded_files_translations ( translatable_id, name, locale)
                VALUES (:translatable_id, :name, :locale)',
                [
                    'translatable_id' => $fileId,
                    'name' => $names[$locale] ?? null,
                    'locale' => $locale,
                ],
                [
                    'translatable_id' => Types::INTEGER,
                    'name' => Types::STRING,
                    'locale' => Types::STRING,
                ],
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
