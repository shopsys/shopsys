<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Tests\FileManipulator;

use Nette\Utils\Json;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\Releaser\FileManipulator\ComposerJsonFileManipulator;
use Symfony\Component\Finder\SplFileInfo;

final class ComposerJsonFileManipulatorTest extends TestCase
{
    private ComposerJsonFileManipulator $composerJsonFileManipulator;

    private string $temporaryFilePath;

    #[Override]
    protected function setUp(): void
    {
        $this->composerJsonFileManipulator = new ComposerJsonFileManipulator();

        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'composer-json-manipulator-test-');

        if ($temporaryFilePath === false) {
            $this->fail('Unable to create a temporary file.');
        }

        $this->temporaryFilePath = $temporaryFilePath;
    }

    #[Override]
    protected function tearDown(): void
    {
        if (file_exists($this->temporaryFilePath)) {
            unlink($this->temporaryFilePath);
        }
    }

    public function testAddRequireIsInsertedAlphabeticallyWithinSameVendor(): void
    {
        $fileInfo = $this->writeComposerJson([
            'require' => [
                'php' => '^8.5',
                'fixture-vendor/alpha' => '1.0.x-dev',
                'fixture-vendor/bravo' => '1.0.x-dev',
                'fixture-vendor/delta' => '1.0.x-dev',
                'other-vendor/library' => '^1.0',
            ],
        ]);

        $this->composerJsonFileManipulator->addRequires($fileInfo, ['fixture-vendor/charlie' => '1.0.x-dev']);

        $this->assertSame(
            [
                'php',
                'fixture-vendor/alpha',
                'fixture-vendor/bravo',
                'fixture-vendor/charlie',
                'fixture-vendor/delta',
                'other-vendor/library',
            ],
            $this->readRequireKeys(),
        );
        $this->assertSame('1.0.x-dev', $this->readRequire()['fixture-vendor/charlie']);
    }

    public function testAddRequireThatSortsAfterAllVendorSiblingsIsInsertedRightAfterTheLastSibling(): void
    {
        $fileInfo = $this->writeComposerJson([
            'require' => [
                'php' => '^8.5',
                'fixture-vendor/alpha' => '1.0.x-dev',
                'fixture-vendor/bravo' => '1.0.x-dev',
                'other-vendor/library' => '^1.0',
            ],
        ]);

        $this->composerJsonFileManipulator->addRequires($fileInfo, ['fixture-vendor/zulu' => '1.0.x-dev']);

        $this->assertSame(
            [
                'php',
                'fixture-vendor/alpha',
                'fixture-vendor/bravo',
                'fixture-vendor/zulu',
                'other-vendor/library',
            ],
            $this->readRequireKeys(),
        );
    }

    public function testAddRequireAppendsToTheEndWhenThereIsNoVendorSibling(): void
    {
        $fileInfo = $this->writeComposerJson([
            'require' => [
                'php' => '^8.5',
                'other-vendor/library' => '^1.0',
            ],
        ]);

        $this->composerJsonFileManipulator->addRequires($fileInfo, ['fixture-vendor/alpha' => '1.0.x-dev']);

        $this->assertSame(['php', 'other-vendor/library', 'fixture-vendor/alpha'], $this->readRequireKeys());
    }

    public function testAddRequireLeavesAnAlreadyPresentRequireUntouched(): void
    {
        $fileInfo = $this->writeComposerJson([
            'require' => [
                'php' => '^8.5',
                'fixture-vendor/alpha' => '1.0.x-dev',
            ],
        ]);

        $this->composerJsonFileManipulator->addRequires($fileInfo, ['fixture-vendor/alpha' => 'dev-some-branch']);

        $this->assertSame(['php', 'fixture-vendor/alpha'], $this->readRequireKeys());
        $this->assertSame('1.0.x-dev', $this->readRequire()['fixture-vendor/alpha']);
    }

    /**
     * @param array<string, mixed> $jsonContent
     */
    private function writeComposerJson(array $jsonContent): SplFileInfo
    {
        file_put_contents($this->temporaryFilePath, Json::encode($jsonContent, pretty: true) . PHP_EOL);

        return new SplFileInfo($this->temporaryFilePath, '', '');
    }

    /**
     * @return array<string, string>
     */
    private function readRequire(): array
    {
        return Json::decode((string)file_get_contents($this->temporaryFilePath), true)['require'];
    }

    /**
     * @return string[]
     */
    private function readRequireKeys(): array
    {
        return array_keys($this->readRequire());
    }
}
