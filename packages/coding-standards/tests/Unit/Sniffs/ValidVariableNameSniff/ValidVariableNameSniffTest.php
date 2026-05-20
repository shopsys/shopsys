<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ValidVariableNameSniff;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\General\ValidVariableNameSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ValidVariableNameSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSniffClassName(): string
    {
        return ValidVariableNameSniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/wrong.inc'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/correct/acronyms_are_allowed.inc'];
    }

    #[DataProvider('getWrongFiles')]
    public function testWrongFiles(string $fileToTest): void
    {
        $this->runWrongFilesTest($fileToTest);
    }

    #[DataProvider('getCorrectFiles')]
    public function testCorrectFiles(string $fileToTest): void
    {
        $this->runCorrectFilesTest($fileToTest);
    }
}
