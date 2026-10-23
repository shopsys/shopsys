<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Filesystem\Flysystem;

use elFinder;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathTraversalDetected;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Filesystem\Flysystem\VolumeDriver;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

// elFinder keeps static state, so tests influence each other when they share a process
#[RunTestsInSeparateProcesses]
class VolumeDriverTest extends TestCase
{
    private const string ROOT_DIRECTORY = 'web/content/wysiwyg';
    private const string VOLUME_ID = 'fls1_';

    private string $projectDir;

    private elFinder $elFinder;

    #[Override]
    protected function setUp(): void
    {
        // the driver registers the elFinderVolumeFlysystem alias that elFinder looks for
        class_exists(VolumeDriver::class);

        $this->projectDir = sys_get_temp_dir() . '/' . uniqid('elfinder-test-', true);
        $symfonyFilesystem = new SymfonyFilesystem();
        $symfonyFilesystem->mkdir([$this->projectDir . '/' . self::ROOT_DIRECTORY, $this->projectDir . '/config', $this->projectDir . '/src', $this->projectDir . '/var']);
        $symfonyFilesystem->dumpFile($this->projectDir . '/' . self::ROOT_DIRECTORY . '/allowed.txt', 'allowed');
        $symfonyFilesystem->dumpFile($this->projectDir . '/config/domains.yaml', 'secret');

        // the same setup as in the application, the filesystem root is the WYSIWYG directory
        $filesystem = new Filesystem(new LocalFilesystemAdapter($this->projectDir . '/' . self::ROOT_DIRECTORY));

        $this->elFinder = new elFinder([
            'roots' => [
                [
                    'driver' => 'Flysystem',
                    'path' => '/',
                    'filesystem' => $filesystem,
                    'cache' => false,
                    'tmbPath' => '',
                    'uploadAllow' => ['text/plain'],
                ],
            ],
        ]);
    }

    #[Override]
    protected function tearDown(): void
    {
        (new SymfonyFilesystem())->remove($this->projectDir);
    }

    public function testRootIsListed(): void
    {
        $result = $this->elFinder->exec('open', ['init' => 1, 'target' => '']);

        $this->assertContains('allowed.txt', array_column($result['files'], 'name'));
    }

    #[DataProvider('forgedPathProvider')]
    public function testFileIsRefusedOutsideRoot(string $forgedPath): void
    {
        $result = $this->exec('file', ['target' => self::hashOfPath($forgedPath), 'download' => 1]);

        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('pointer', $result);
    }

    #[DataProvider('forgedDirectoryProvider')]
    public function testOpenIsRefusedOutsideRoot(string $forgedDirectory): void
    {
        $result = $this->exec('open', ['target' => self::hashOfPath($forgedDirectory)]);

        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('files', $result);
    }

    public function testUploadIsAllowedInsideRoot(): void
    {
        $rootHash = $this->elFinder->exec('open', ['init' => 1, 'target' => ''])['cwd']['hash'];

        $result = $this->upload($rootHash);

        $this->assertArrayNotHasKey('error', $result);
        $this->assertFileExists($this->projectDir . '/' . self::ROOT_DIRECTORY . '/evil.txt');
    }

    #[DataProvider('forgedDirectoryProvider')]
    public function testUploadIsRefusedOutsideRoot(string $forgedDirectory): void
    {
        $result = $this->upload(self::hashOfPath($forgedDirectory));

        $this->assertEmpty($result['added'] ?? []);
        $this->assertFileDoesNotExist($this->projectDir . '/config/evil.txt');
        $this->assertFileDoesNotExist($this->projectDir . '/var/evil.txt');
    }

    #[DataProvider('forgedPathProvider')]
    public function testRemoveIsRefusedOutsideRoot(string $forgedPath): void
    {
        $this->exec('rm', ['targets' => [self::hashOfPath($forgedPath)]]);

        $this->assertFileExists($this->projectDir . '/config/domains.yaml');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function forgedPathProvider(): iterable
    {
        yield 'path from the project root' => ['config/domains.yaml'];

        yield 'traversal from the root' => ['../../../config/domains.yaml'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function forgedDirectoryProvider(): iterable
    {
        yield 'config directory' => ['config'];

        yield 'traversal to the config directory' => ['../../../config'];

        yield 'traversal to the var directory' => ['../../../var'];
    }

    /**
     * @return mixed[]
     */
    private function upload(string $targetHash): array
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($temporaryFile, 'x');

        $result = $this->exec('upload', [
            'target' => $targetHash,
            'FILES' => ['upload' => ['name' => ['evil.txt'], 'tmp_name' => [$temporaryFile], 'error' => [0], 'size' => [1], 'type' => ['text/plain']]],
            'upload_path' => [],
        ]);

        @unlink($temporaryFile);

        return $result;
    }

    /**
     * A traversal above the filesystem root is refused by the filesystem with an exception, any other forged path ends with an error
     *
     * @param mixed[] $arguments
     * @return mixed[]
     */
    private function exec(string $command, array $arguments): array
    {
        try {
            return $this->elFinder->exec($command, $arguments);
        } catch (PathTraversalDetected $exception) {
            return ['error' => $exception->getMessage()];
        }
    }

    private static function hashOfPath(string $path): string
    {
        return self::VOLUME_ID . rtrim(strtr(base64_encode($path), '+/=', '-_.'), '.');
    }
}
