<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Tests\FileManipulator\UpgradeFileManipulator;

use PharIo\Version\Version;
use PHPUnit\Framework\TestCase;
use Shopsys\Releaser\FileManipulator\VersionUpgradeFileManipulator;
use Symplify\SmartFileSystem\SmartFileInfo;

class VersionUpgradeFileManipulatorTest extends TestCase
{
    private VersionUpgradeFileManipulator $versionUpgradeFileManipulator;

    protected function setUp(): void
    {
        $this->versionUpgradeFileManipulator = new VersionUpgradeFileManipulator();
    }

    public function testProcessFileToString(): void
    {
        $changedContent = $this->versionUpgradeFileManipulator->processFileToString(
            new SmartFileInfo(__DIR__ . '/Source/UPGRADE-version-before.md'),
            new Version('v7.0.0-beta5'),
            '7.0',
        );

        $this->assertStringMatchesFormatFile(__DIR__ . '/Source/UPGRADE-version-after.md', $changedContent);
    }
}
