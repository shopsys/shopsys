<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForceLateStaticBindingForProtectedConstantsSniffTest extends AbstractSniffTestCase
{
    /**
     * @param string $fixedFileName
     * @param string $inputFileName
     */
    #[DataProvider('getFixableFiles')]
    public function testFixableFiles(string $fixedFileName, string $inputFileName): void
    {
        $file = $this->doRunSniff($inputFileName);

        self::assertGreaterThan(0, $file->getErrorCount(), $inputFileName . ' should raise error');

        $file->fixer->fixFile();

        self::assertStringEqualsFile($fixedFileName, $file->fixer->getContents());
    }

    /**
     * @return iterable
     */
    public static function getFixableFiles(): iterable
    {
        yield [__DIR__ . '/fixed/SingleValue.php', __DIR__ . '/wrong/SingleValue.php'];

        yield [__DIR__ . '/fixed/SelfWithMethodsAndVariables.php', __DIR__ . '/wrong/SelfWithMethodsAndVariables.php'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSniffClassName(): string
    {
        return ForceLateStaticBindingForProtectedConstantsSniff::class;
    }
}
