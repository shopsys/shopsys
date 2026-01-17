<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForbiddenExitSniff;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ForbiddenExitSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForbiddenExitSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSniffClassName(): string
    {
        return ForbiddenExitSniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/wrong.php.inc'];
    }

    #[DataProvider('getWrongFiles')]
    public function testWrongFiles(string $fileToTest): void
    {
        $this->runWrongFilesTest($fileToTest);
    }
}
