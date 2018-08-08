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
            'MigrationsNamespace'
        );
    }

    public function testExistingMigrationsLocation(): void
    {
        $this->kernelReturnsOneBundle('Test\\MockBundle', 'test/MockBundle');
        $this->filesystemSaysPathExists('test/MockBundle/MigrationsDirectory');

        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();

        $this->assertCount(1, $migrationsLocations);
    }

    public function testNonExistingMigrationsLocation(): void
    {
        $this->kernelReturnsOneBundle('Test\\MockBundle', 'test/MockBundle');
        $this->filesystemSaysPathExists('test/MockBundle/MigrationsDirectory', false);

        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();

        $this->assertEmpty($migrationsLocations);
    }

    public function testMultipleMigrationsLocations(): void
    {
        $bundle = $this->createBundleMock('Test\\MockBundle', 'test/MockBundle');
        $this->kernelMock->method('getBundles')->willReturn([$bundle, $bundle, $bundle]);
        $this->filesystemSaysEveryPathExists();

        $migrationsLocations = $this->migrationsLocator->getMigrationsLocations();

        $this->assertCount(3, $migrationsLocations);
    }

    public function testMigrationsLocationParameters(): void
    {
        $this->kernelReturnsOneBundle('Test\\MockBundle', 'test/MockBundle');
        $this->filesystemSaysEveryPathExists();

        list($migrationsLocation) = $this->migrationsLocator->getMigrationsLocations();

        $this->assertEquals('Test\\MockBundle\\MigrationsNamespace', $migrationsLocation->getNamespace());
        $this->assertEquals('test/MockBundle/MigrationsDirectory', $migrationsLocation->getDirectory());
    }
    
    private function kernelReturnsOneBundle(string $namespace, string $path): void
    {
        $this->kernelMock->method('getBundles')
            ->willReturn([$this->createBundleMock($namespace, $path)]);
    }
    
    private function filesystemSaysPathExists(string $path, bool $exists = true): void
    {
        $this->filesystemMock->method('exists')
            ->with($path)
            ->willReturn($exists);
    }
    
    private function filesystemSaysEveryPathExists(bool $exists = true): void
    {
        $this->filesystemMock->method('exists')
            ->willReturn($exists);
    }

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createBundleMock(string $namespace, string $path)
    {
        $bundleMock = $this->createMock(BundleInterface::class);

        $bundleMock->method('getNamespace')
            ->willReturn($namespace);
        $bundleMock->method('getPath')
            ->willReturn($path);

        return $bundleMock;
    }
}
