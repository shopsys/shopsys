<?php

namespace Shopsys\MigrationBundle\Component\Doctrine\Migrations;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class MigrationsLocator
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $relativeDirectory;

    /**
     * @var string
     */
    private $relativeNamespace;

    /**
     * @var string
     */
    private $applicationMigrationNamespace;

    /**
     * @var string
     */
    private $applicationMigrationPath;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string $relativeDirectory
     * @param string $relativeNamespace
     * @param string $applicationMigrationNamespace
     * @param string $applicationMigrationPath
     */
    public function __construct(
        KernelInterface $kernel,
        Filesystem $filesystem,
        $relativeDirectory,
        $relativeNamespace,
        $applicationMigrationNamespace,
        $applicationMigrationPath
    ) {
        $this->kernel = $kernel;
        $this->filesystem = $filesystem;
        $this->relativeDirectory = $relativeDirectory;
        $this->relativeNamespace = $relativeNamespace;
        $this->applicationMigrationNamespace = $applicationMigrationNamespace;
        $this->applicationMigrationPath = $applicationMigrationPath;
    }

    /**
     * Gets possible locations of migration classes to allow multiple sources of migrations.
     *
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocation[]
     */
    public function getMigrationsLocations()
    {
        $migrationsLocations = [];
        $migrationsLocations[] = $this->getApplicationMigrationLocation();
        foreach ($this->kernel->getBundles() as $bundle) {
            $migrationsLocation = $this->createMigrationsLocation($bundle);
            if ($this->filesystem->exists($migrationsLocation->getDirectory())) {
                $migrationsLocations[] = $migrationsLocation;
            }
        }

        return $migrationsLocations;
    }

    /**
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocation
     */
    public function getApplicationMigrationLocation(): MigrationsLocation
    {
        return new MigrationsLocation(
            $this->applicationMigrationPath,
            $this->applicationMigrationNamespace
        );
    }

    /**
     * Creates a locations of migration classes for a particular bundle.
     *
     * @param \Symfony\Component\HttpKernel\Bundle\BundleInterface $bundle
     * @return \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocation
     */
    public function createMigrationsLocation(BundleInterface $bundle)
    {
        return new MigrationsLocation(
            $bundle->getPath() . '/' . $this->relativeDirectory,
            $bundle->getNamespace() . '\\' . $this->relativeNamespace
        );
    }
}
