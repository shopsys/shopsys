<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Filesystem;

use InvalidArgumentException;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Filesystem\DirectoryRestrictedFilesystem;
use Shopsys\FrameworkBundle\Component\Filesystem\Exception\PathOutsideAllowedDirectoryException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

class DirectoryRestrictedFilesystemTest extends TestCase
{
    private const string ALLOWED_DIRECTORY = 'web/content/wysiwyg';

    private string $projectDir;

    private DirectoryRestrictedFilesystem $filesystem;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/' . uniqid('restricted-filesystem-test-', true);
        $symfonyFilesystem = new SymfonyFilesystem();
        $symfonyFilesystem->dumpFile($this->projectDir . '/' . self::ALLOWED_DIRECTORY . '/allowed.txt', 'allowed');
        $symfonyFilesystem->dumpFile($this->projectDir . '/config/domains.yaml', 'secret');

        $this->filesystem = new DirectoryRestrictedFilesystem(
            new Filesystem(new LocalFilesystemAdapter($this->projectDir)),
            self::ALLOWED_DIRECTORY,
        );
    }

    protected function tearDown(): void
    {
        (new SymfonyFilesystem())->remove($this->projectDir);
    }

    public function testAllowedDirectoryIsAccessible(): void
    {
        $this->filesystem->write(self::ALLOWED_DIRECTORY . '/new.txt', 'new');

        $this->assertSame('allowed', $this->filesystem->read(self::ALLOWED_DIRECTORY . '/allowed.txt'));
        $this->assertTrue($this->filesystem->fileExists(self::ALLOWED_DIRECTORY . '/new.txt'));
        $this->assertCount(2, $this->filesystem->listContents(self::ALLOWED_DIRECTORY)->toArray());

        $this->filesystem->delete(self::ALLOWED_DIRECTORY . '/new.txt');

        $this->assertFalse($this->filesystem->fileExists(self::ALLOWED_DIRECTORY . '/new.txt'));
    }

    public function testExistenceChecksOutsideAllowedDirectoryReturnFalse(): void
    {
        $this->assertFalse($this->filesystem->fileExists('config/domains.yaml'));
        $this->assertFalse($this->filesystem->has('config'));
        $this->assertFalse($this->filesystem->directoryExists('config'));
    }

    public function testOperationsOutsideAllowedDirectoryAreRefused(): void
    {
        $operations = [
            'read' => fn () => $this->filesystem->read('config/domains.yaml'),
            'readStream' => fn () => $this->filesystem->readStream('config/domains.yaml'),
            'listContents' => fn () => $this->filesystem->listContents('config'),
            'fileSize' => fn () => $this->filesystem->fileSize('config/domains.yaml'),
            'write' => fn () => $this->filesystem->write('config/new.txt', 'x'),
            'delete' => fn () => $this->filesystem->delete('config/domains.yaml'),
            'deleteDirectory' => fn () => $this->filesystem->deleteDirectory('config'),
            'createDirectory' => fn () => $this->filesystem->createDirectory('config/new'),
            'copy from outside' => fn () => $this->filesystem->copy('config/domains.yaml', self::ALLOWED_DIRECTORY . '/copy.yaml'),
            'copy to outside' => fn () => $this->filesystem->copy(self::ALLOWED_DIRECTORY . '/allowed.txt', 'config/copy.txt'),
            'move to outside' => fn () => $this->filesystem->move(self::ALLOWED_DIRECTORY . '/allowed.txt', 'config/moved.txt'),
            'traversal' => fn () => $this->filesystem->read(self::ALLOWED_DIRECTORY . '/../../../config/domains.yaml'),
        ];

        foreach ($operations as $name => $operation) {
            try {
                $operation();
                $this->fail(sprintf('Operation "%s" outside of the allowed directory was not refused.', $name));
            } catch (PathOutsideAllowedDirectoryException) {
                $this->addToAssertionCount(1);
            }
        }

        $this->assertFileExists($this->projectDir . '/config/domains.yaml');
        $this->assertFileExists($this->projectDir . '/' . self::ALLOWED_DIRECTORY . '/allowed.txt');
    }

    #[DataProvider('emptyDirectoryProvider')]
    public function testEmptyAllowedDirectoryIsRejected(string $allowedDirectory): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DirectoryRestrictedFilesystem(new Filesystem(new LocalFilesystemAdapter($this->projectDir)), $allowedDirectory);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptyDirectoryProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'root' => ['/'];
    }
}
