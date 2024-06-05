<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForbiddenDumpSniff;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ForbiddenDumpSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForbiddenDumpSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getSniffClassName(): string
    {
        return ForbiddenDumpSniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/d.php.inc'];

        yield [__DIR__ . '/wrong/dump.php.inc'];

        yield [__DIR__ . '/wrong/print_r.php.inc'];

        yield [__DIR__ . '/wrong/var_dump.php.inc'];

        yield [__DIR__ . '/wrong/var_export.php.inc'];
    }

    /**
     * @param string $fileToTest
     */
    #[DataProvider('getWrongFiles')]
    public function testWrongFiles(string $fileToTest): void
    {
        $this->runWrongFilesTest($fileToTest);
    }
}
