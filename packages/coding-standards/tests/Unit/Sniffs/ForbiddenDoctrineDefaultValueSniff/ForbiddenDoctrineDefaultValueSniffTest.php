<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Sniffs\ForbiddenDoctrineDefaultValueSniff;

use Symplify\EasyCodingStandardTester\Testing\AbstractCheckerTestCase;

class ForbiddenDoctrineDefaultValueSniffTest extends AbstractCheckerTestCase
{
    public function testWrongFile(): void
    {
        $this->doTestWrongFile(__DIR__ . '/wrong/default_value_annotation.php');
        $this->doTestWrongFile(__DIR__ . '/wrong/different_order_annotation.php');
        $this->doTestWrongFile(__DIR__ . '/wrong/multiline_annotation.php');
        $this->doTestWrongFile(__DIR__ . '/wrong/spaces_around_annotation.php');
        $this->doTestWrongFile(__DIR__ . '/wrong/split_annotation.php');
    }

    public function testCorrectFile(): void
    {
        $this->doTestCorrectFile(__DIR__ . '/correct/missing_default_value_annotation.php');
        $this->doTestCorrectFile(__DIR__ . '/correct/invalid_docblock_annotation.php');
    }

    /**
     * @return string
     */
    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
