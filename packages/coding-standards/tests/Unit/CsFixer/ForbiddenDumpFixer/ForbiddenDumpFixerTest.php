<?php

declare(strict_types=1);

namespace Tests\CodingStandards\CsFixer\ForbiddenDumpFixer;

use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ForbiddenDumpFixerTest extends AbstractCheckerTestCase
{
    public function testFix(): void
    {
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/fixed/fixed.html.twig'));
    }

    public function testCorrect(): void
    {
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/correct/correct.html.twig'));
    }

    /**
     * @return string
     */
    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
