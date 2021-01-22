<?php

declare(strict_types=1);

namespace Tests\CodingStandards\CsFixer\OrmJoinColumnRequireNullableFixer;

use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

final class OrmJoinColumnRequireNullableFixerTest extends AbstractCheckerTestCase
{
    public function testFix(): void
    {
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/fixed/many_to_one_missing_join_column.php.test'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/fixed/many_to_one_missing_nullable_param.php.test'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/fixed/one_to_one_missing_join_column.php.test'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/fixed/one_to_one_missing_nullable_param.php.test'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/fixed/one_to_one_multiline_missing_nullable_param.php.test'));
    }

    public function testCorrect(): void
    {
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/correct/one_to_many.php'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/correct/many_to_one_missing_join_column.php'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/correct/many_to_one_missing_nullable_param.php'));
        $this->doTestFileInfo(new SmartFileInfo(__DIR__ . '/correct/one_to_one_missing_nullable_param.php'));
    }

    /**
     * @return string
     */
    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
