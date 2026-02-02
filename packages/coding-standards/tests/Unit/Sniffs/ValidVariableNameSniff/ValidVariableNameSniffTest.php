<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ValidVariableNameSniff;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ValidVariableNameSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ValidVariableNameSniffTest extends AbstractSniffTestCase
{
    #[Override]
    protected function getSniffClassName(): string
    {
        return ValidVariableNameSniff::class;
    }

    public static function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/wrong.inc'];
    }

    #[DataProvider('getWrongFiles')]
    public function testWrongFiles(string $fileToTest): void
    {
        $this->runWrongFilesTest($fileToTest);
    }
}
