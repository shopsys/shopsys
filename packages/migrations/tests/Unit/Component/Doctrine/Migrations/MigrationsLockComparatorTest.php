<?php

declare(strict_types=1);

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use Doctrine\Migrations\Version\Version;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLockComparator;

class MigrationsLockComparatorTest extends TestCase
{
    private const EXPECTED_RESULT_LESS_THAN_ZERO = 'lessThanZero';
    private const EXPECTED_RESULT_GREATER_THAN_ZERO = 'greaterThanZero';

    /**
     * @param string[] $orderedMigrationClassesFromLock
     * @param \Doctrine\Migrations\Version\Version $versionA
     * @param \Doctrine\Migrations\Version\Version $versionB
     * @param string $expectedResult
     */
    #[DataProvider('compareDataProvider')]
    public function testCompare(
        array $orderedMigrationClassesFromLock,
        Version $versionA,
        Version $versionB,
        string $expectedResult,
    ): void {
        $migrationsLockMock = $this->createMock(MigrationsLock::class);
        $migrationsLockMock->method('getOrderedInstalledMigrationClasses')->willReturn($orderedMigrationClassesFromLock);
        $migrationsLockComparator = new MigrationsLockComparator($migrationsLockMock);
        $actualResult = $migrationsLockComparator->compare($versionA, $versionB);

        if ($expectedResult === self::EXPECTED_RESULT_LESS_THAN_ZERO) {
            $this->assertLessThan(0, $actualResult);
        }

        if ($expectedResult === self::EXPECTED_RESULT_GREATER_THAN_ZERO) {
            $this->assertGreaterThan(0, $actualResult);
        }
    }

    /**
     * @return \Iterator
     */
    public static function compareDataProvider(): Iterator
    {
        yield [
            'orderedMigrationClassesFromLock' => ['Version1', 'Version2'],
            'versionA' => new Version('Version1'),
            'versionB' => new Version('Version2'),
            'expectedResult' => self::EXPECTED_RESULT_LESS_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => ['Version2', 'Version1'],
            'versionA' => new Version('Version1'),
            'versionB' => new Version('Version2'),
            'expectedResult' => self::EXPECTED_RESULT_GREATER_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => [],
            'versionA' => new Version('Version1'),
            'versionB' => new Version('Version2'),
            'expectedResult' => self::EXPECTED_RESULT_LESS_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => [],
            'versionA' => new Version('Version2'),
            'versionB' => new Version('Version1'),
            'expectedResult' => self::EXPECTED_RESULT_GREATER_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => [],
            'versionA' => new Version('Namespace2\Version1'),
            'versionB' => new Version('Namespace1\Version2'),
            'expectedResult' => self::EXPECTED_RESULT_LESS_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => [],
            'versionA' => new Version('Namespace2\Version2'),
            'versionB' => new Version('Namespace1\Version1'),
            'expectedResult' => self::EXPECTED_RESULT_GREATER_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => ['Namespace2\Version2', 'Namespace1\Version1'],
            'versionA' => new Version('Namespace2\Version2'),
            'versionB' => new Version('Namespace1\Version1'),
            'expectedResult' => self::EXPECTED_RESULT_LESS_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => ['Namespace1\Version1', 'Namespace2\Version2'],
            'versionA' => new Version('Namespace2\Version2'),
            'versionB' => new Version('Namespace1\Version1'),
            'expectedResult' => self::EXPECTED_RESULT_GREATER_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => ['Namespace2\Version2', 'Namespace1\Version1'],
            'versionA' => new Version('Namespace1\Version1'),
            'versionB' => new Version('Namespace2\Version2'),
            'expectedResult' => self::EXPECTED_RESULT_GREATER_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => ['Namespace1\Version1'],
            'versionA' => new Version('Namespace2\Version2'),
            'versionB' => new Version('Namespace1\Version1'),
            'expectedResult' => self::EXPECTED_RESULT_GREATER_THAN_ZERO,
        ];

        yield [
            'orderedMigrationClassesFromLock' => ['Namespace2\Version2'],
            'versionA' => new Version('Namespace2\Version2'),
            'versionB' => new Version('Namespace1\Version1'),
            'expectedResult' => self::EXPECTED_RESULT_LESS_THAN_ZERO,
        ];
    }
}
