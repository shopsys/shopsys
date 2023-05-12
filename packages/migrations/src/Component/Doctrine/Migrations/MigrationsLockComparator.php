<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;

class MigrationsLockComparator implements Comparator
{
    /**
     * @param \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLock $migrationsLock
     */
    public function __construct(protected readonly MigrationsLock $migrationsLock)
    {
    }

    /**
     * @param \Doctrine\Migrations\Version\Version $a
     * @param \Doctrine\Migrations\Version\Version $b
     * @return int
     */
    public function compare(Version $a, Version $b): int
    {
        $installedMigrationsAccordingToLock = $this->migrationsLock->getOrderedInstalledMigrationClasses();
        $aIndex = array_search((string)$a, $installedMigrationsAccordingToLock, true);
        $bIndex = array_search((string)$b, $installedMigrationsAccordingToLock, true);
        if (count($installedMigrationsAccordingToLock) === 0 || $aIndex === false && $bIndex === false) {
            return $this->compareVersionsAlphabetically($a, $b);
        }
        if ($aIndex === false && $bIndex !== false) {
            return 1;
        }
        if ($aIndex !== false && $bIndex === false) {
            return -1;
        }

        return $aIndex - $bIndex;
    }

    /**
     * @param \Doctrine\Migrations\Version\Version $a
     * @param \Doctrine\Migrations\Version\Version $b
     * @return int
     */
    protected function compareVersionsAlphabetically(Version $a, Version $b): int
    {
        return strcmp($this->getVersionWithoutNamespace($a), $this->getVersionWithoutNamespace($b));
    }

    /**
     * @param \Doctrine\Migrations\Version\Version $version
     * @return string
     */
    protected function getVersionWithoutNamespace(Version $version): string
    {
        $versionString = (string)$version;

        $lastBackslashPosition = strrpos($versionString, '\\');
        if ($lastBackslashPosition === false) {
            return $versionString;
        }

        return substr($versionString, $lastBackslashPosition + 1);
    }
}
