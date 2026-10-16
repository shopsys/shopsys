<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Filesystem\Flysystem;

use elFinder;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
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

    protected function setUp(): void
    {
        // the driver registers the elFinderVolumeFlysystem alias that elFinder looks for
        class_exists(VolumeDriver::class);

        $this->projectDir = sys_get_temp_dir() . '/' . uniqid('elfinder-test-', true);
        $symfonyFilesystem = new SymfonyFilesystem();
        $symfonyFilesystem->mkdir([$this->projectDir . '/' . self::ROOT_DIRECTORY, $this->projectDir . '/config', $this->projectDir . '/src', $this->projectDir . '/var']);
        $symfonyFilesystem->dumpFile($this->projectDir . '/' . self::ROOT_DIRECTORY . '/allowed.txt', 'allowed');
        $symfonyFilesystem->dumpFile($this->projectDir . '/config/domains.yaml', 'secret');

        // the filesystem is intentionally not restricted, so the volume driver itself is tested
        $filesystem = new Filesystem(new LocalFilesystemAdapter($this->projectDir));

        $this->elFinder = new elFinder([
            'roots' => [
                [
                    'driver' => 'Flysystem',
                    'path' => self::ROOT_DIRECTORY,
                    'filesystem' => $filesystem,
                    'cache' => false,
                    'tmbPath' => '',
                    'uploadAllow' => ['text/plain'],
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        (new SymfonyFilesystem())->remove($this->projectDir);
    }

    public function testRootIsListed(): void
    {
        $result = $this->elFinder->exec('open', ['init' => 1, 'target' => '']);

        $this->assertSame(self::VOLUME_ID . self::hash(self::ROOT_DIRECTORY), $result['cwd']['hash']);
        $this->assertContains('allowed.txt', array_column($result['files'], 'name'));
    }

    /**
     * @param string $forgedPath
     */
    #[DataProvider('forgedPathProvider')]
    public function testFileIsRefusedOutsideRoot(string $forgedPath): void
    {
        $result = $this->elFinder->exec('file', ['target' => self::hashOfPath($forgedPath), 'download' => 1]);

        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('pointer', $result);
    }

    #[DataProvider('forgedDirectoryProvider')]
    public function testOpenIsRefusedOutsideRoot(string $forgedDirectory): void
    {
        $result = $this->elFinder->exec('open', ['target' => self::hashOfPath($forgedDirectory)]);

        $this->assertNotSame(self::hashOfPath($forgedDirectory), $result['cwd']['hash'] ?? null);
        $this->assertNotContains('domains.yaml', array_column($result['files'] ?? [], 'name'));
    }

    public function testUploadIsAllowedInsideRoot(): void
    {
        $result = $this->upload(self::hashOfPath(self::ROOT_DIRECTORY));

        $this->assertArrayNotHasKey('error', $result);
        $this->assertFileExists($this->projectDir . '/' . self::ROOT_DIRECTORY . '/evil.txt');
    }

    #[DataProvider('forgedDirectoryProvider')]
    public function testUploadIsRefusedOutsideRoot(string $forgedDirectory): void
    {
        $result = $this->upload(self::hashOfPath($forgedDirectory));

        $this->assertSame([], $result['added']);
        $this->assertNotEmpty($result['warning']);
        $this->assertFileDoesNotExist($this->projectDir . '/' . $forgedDirectory . '/evil.txt');
    }

    #[DataProvider('forgedPathProvider')]
    public function testRemoveIsRefusedOutsideRoot(string $forgedPath): void
    {
        $this->elFinder->exec('rm', ['targets' => [self::hashOfPath($forgedPath)]]);

        $this->assertFileExists($this->projectDir . '/config/domains.yaml');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function forgedPathProvider(): iterable
    {
        yield 'config file' => ['config/domains.yaml'];
        yield 'traversal from the root' => [self::ROOT_DIRECTORY . '/../../../config/domains.yaml'];
        yield 'prefix of the root name' => [self::ROOT_DIRECTORY . '-evil/domains.yaml'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function forgedDirectoryProvider(): iterable
    {
        yield 'config directory' => ['config'];
        yield 'var directory' => ['var'];
        yield 'src directory' => ['src'];
        yield 'traversal from the root' => [self::ROOT_DIRECTORY . '/../../../var'];
    }

    /**
     * @return mixed[]
     */
    private function upload(string $targetHash): array
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($temporaryFile, 'x');

        $result = $this->elFinder->exec('upload', [
            'target' => $targetHash,
            'FILES' => ['upload' => ['name' => ['evil.txt'], 'tmp_name' => [$temporaryFile], 'error' => [0], 'size' => [1], 'type' => ['text/plain']]],
            'upload_path' => [],
        ]);

        @unlink($temporaryFile);

        return $result;
    }

    private static function hashOfPath(string $path): string
    {
        return self::VOLUME_ID . self::hash($path);
    }

    private static function hash(string $path): string
    {
        return rtrim(strtr(base64_encode($path), '+/=', '-_.'), '.');
    }
}
