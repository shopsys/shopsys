<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForceLateStaticBindingForProtectedConstantsSniff;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\General\ForceLateStaticBindingForProtectedConstantsSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ForceLateStaticBindingForProtectedConstantsSniffTest extends AbstractSniffTestCase
{
    #[DataProvider('getFixableFiles')]
    public function testFixableFiles(string $fixedFileName, string $inputFileName): void
    {
        $this->runFixableFilesTest($fixedFileName, $inputFileName);
    }

    public static function getFixableFiles(): iterable
    {
        yield [__DIR__ . '/fixed/SingleValue.php', __DIR__ . '/wrong/SingleValue.php'];

        yield [__DIR__ . '/fixed/SelfWithMethodsAndVariables.php', __DIR__ . '/wrong/SelfWithMethodsAndVariables.php'];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSniffClassName(): string
    {
        return ForceLateStaticBindingForProtectedConstantsSniff::class;
    }
}
