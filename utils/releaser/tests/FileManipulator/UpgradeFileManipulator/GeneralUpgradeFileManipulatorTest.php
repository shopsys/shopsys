<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Tests\FileManipulator\UpgradeFileManipulator;

use PharIo\Version\Version;
use PHPUnit\Framework\TestCase;
use Shopsys\Releaser\FileManipulator\GeneralUpgradeFileManipulator;
use Symplify\SmartFileSystem\SmartFileInfo;

class GeneralUpgradeFileManipulatorTest extends TestCase
{
    private GeneralUpgradeFileManipulator $generalUpgradeFileManipulator;

    protected function setUp(): void
    {
        $this->generalUpgradeFileManipulator = new GeneralUpgradeFileManipulator();
    }

    public function test(): void
    {
        $changedContent = $this->generalUpgradeFileManipulator->updateLinks(
            new SmartFileInfo(__DIR__ . '/Source/UPGRADE-general-before.md'),
            new Version('v7.0.0-beta5'),
            'v7.0.0-dev',
        );

        $this->assertStringMatchesFormatFile(__DIR__ . '/Source/UPGRADE-general-after.md', $changedContent);
    }
}
