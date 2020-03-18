<?php

namespace Tests\MigrationBundle\Unit\Component\Doctrine\Migrations;

use PHPUnit\Framework\TestCase;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class MigrationsLocatorTest extends TestCase
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $kernelMock;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystemMock;

    /**
     * @var \Shopsys\MigrationBundle\Component\Doctrine\Migrations\MigrationsLocator
     */
    private $migrationsLocator;

    protected function setUp(): void
    {
        $this->kernelMock = $this->createMock(KernelInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->migrationsLocator = new MigrationsLocator(
            $this->kernelMock,
            $this->filesystemMock,
            'MigrationsDirectory',
            'MigrationsNamespace',
            'AppNamespace\Migrations',
            'src/Resources/MigrationsDirectory'
        );
    }

    public function testExistingMigrationsLocation()
    {
        $this->kernelReturnsOneBundle('Test\\MockBundle', 'test/MockBundle');
        $this->filesystemSaysPathExists('test/MockBundle/MigrationsDirectory');

        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();
        $this->assertCount(2, $migrationsLocations);
    }

    public function testNonExistingBundleMigrationsLocation()
    {
        $this->kernelReturnsOneBundle('Test\\MockBundle', 'test/MockBundle');
        $this->filesystemSaysPathExists('test/MockBundle/MigrationsDirectory', false);
        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();
        $this->assertCount(1, $migrationsLocations);
    }

    public function testMultipleMigrationsLocations()
    {
        $bundle = $this->createBundleMock('Test\\MockBundle', 'test/MockBundle');
        $this->kernelMock->method('getBundles')->willReturn([$bundle, $bundle, $bundle]);
        $this->filesystemSaysEveryPathExists();

        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();
        $this->assertCount(4, $migrationsLocations);
    }

    public function testMigrationsLocationParameters()
    {
        $this->kernelReturnsOneBundle('Test\\MockBundle', 'test/MockBundle');
        $this->filesystemSaysEveryPathExists();
        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();
        $appMigration = $migrationsLocations[0];
        $bundleMigration = $migrationsLocations[1];
        $this->assertEquals('AppNamespace\\Migrations', $appMigration->getNamespace());
        $this->assertEquals('src/Resources/MigrationsDirectory', $appMigration->getDirectory());
        $this->assertEquals('Test\\MockBundle\\MigrationsNamespace', $bundleMigration->getNamespace());
        $this->assertEquals('test/MockBundle/MigrationsDirectory', $bundleMigration->getDirectory());
    }

    /**
     * @param string $namespace
     * @param string $path
     */
    private function kernelReturnsOneBundle($namespace, $path)
    {
        $this->kernelMock->method('getBundles')
            ->willReturn([$this->createBundleMock($namespace, $path)]);
    }

    /**
     * @param string $path
     * @param bool $exists
     */
    private function filesystemSaysPathExists($path, $exists = true)
    {
        $this->filesystemMock->method('exists')
            ->with($path)
            ->willReturn($exists);
    }

    /**
     * @param bool $exists
     */
    private function filesystemSaysEveryPathExists($exists = true)
    {
        $this->filesystemMock->method('exists')
            ->willReturn($exists);
    }

    /**
     * @param string $namespace
     * @param string $path
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createBundleMock($namespace, $path)
    {
        $bundleMock = $this->createMock(BundleInterface::class);

        $bundleMock->method('getNamespace')
            ->willReturn($namespace);
        $bundleMock->method('getPath')
            ->willReturn($path);

        return $bundleMock;
    }
}
