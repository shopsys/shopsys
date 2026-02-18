<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Finder\MigrationFinder;
use Doctrine\Migrations\Version\Version;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLockRepository;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000001;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000002;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000003;
use Tests\MigrationBundle\Unit\Component\Doctrine\Migrations\Resources\Version20180101000004;

class MigrationsLockRepositoryTest extends AbstractMigrationLockTestCase
{
    /**
     * @param string[] $foundMigrationClasses
     * @param string[] $expectedMigrationClasses
     */
    #[DataProvider('getMigrationsDataProvider')]
    public function testGetMigrations(array $foundMigrationClasses, array $expectedMigrationClasses): void
    {
        $migrationsLockRepository = $this->getMigrationsLockRepository($foundMigrationClasses);

        $availableMigrationsSet = $migrationsLockRepository->getMigrations();

        $this->assertSame(count($expectedMigrationClasses), count($availableMigrationsSet->getItems()));

        foreach ($expectedMigrationClasses as $expectedMigrationClass) {
            $this->assertTrue($availableMigrationsSet->hasMigration(new Version($expectedMigrationClass)));
        }
    }

    public static function getMigrationsDataProvider(): Iterator
    {
        yield [
            'foundMigrationClasses' => [],
            'expectedMigrationClasses' => [],
        ];

        yield [
            'foundMigrationClasses' => [
                Version20180101000001::class,
                Version20180101000002::class,
                Version20180101000003::class,
                Version20180101000004::class,
            ],
            'expectedMigrationClasses' => [
                Version20180101000001::class,
                Version20180101000003::class,
                Version20180101000004::class,
            ],
        ];
    }

    /**
     * @param string[] $foundMigrationClasses
     */
    private function getMigrationsLockRepository(array $foundMigrationClasses): MigrationsLockRepository
    {
        $dependencyFactoryStub = $this->createStub(DependencyFactory::class);
        $migrationFinderStub = $this->createStub(MigrationFinder::class);
        $migrationFinderStub->method('findMigrations')->willReturn($foundMigrationClasses);
        $configuration = new Configuration();
        $configuration->addMigrationsDirectory('namespace', 'path');
        $dependencyFactoryStub->method('getMigrationsFinder')->willReturn($migrationFinderStub);
        $dependencyFactoryStub->method('getConfiguration')->willReturn($configuration);

        return new MigrationsLockRepository($this->migrationsLock, $dependencyFactoryStub);
    }
}
