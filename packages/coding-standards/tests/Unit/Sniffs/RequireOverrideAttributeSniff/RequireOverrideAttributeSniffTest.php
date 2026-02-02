<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeSniff;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\RequireOverrideAttributeSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class RequireOverrideAttributeSniffTest extends AbstractSniffTestCase
{
    #[Override]
    protected function getSniffClassName(): string
    {
        return RequireOverrideAttributeSniff::class;
    }

    public static function getFixableFiles(): iterable
    {
        yield [__DIR__ . '/Fixed/SimpleWithParentClass.php', __DIR__ . '/Wrong/SimpleWithParentClass.php'];

        yield [__DIR__ . '/Fixed/ClassWithInterface.php', __DIR__ . '/Wrong/ClassWithInterface.php'];

        yield [__DIR__ . '/Fixed/CombinedParentsClass.php', __DIR__ . '/Wrong/CombinedParentsClass.php'];

        yield [__DIR__ . '/Fixed/ChainedClass.php', __DIR__ . '/Wrong/ChainedClass.php'];

        yield [__DIR__ . '/Fixed/ChainedInterfacesClass.php', __DIR__ . '/Wrong/ChainedInterfacesClass.php'];

        yield [__DIR__ . '/Correct/NoMagicMethodsOverride.php'];

        yield [__DIR__ . '/Correct/NoOverrideNeeded.php'];
    }

    #[DataProvider('getFixableFiles')]
    public function testFixableFiles(string $fixedFileName, ?string $inputFileName = null): void
    {
        $this->runFixableFilesTest($fixedFileName, $inputFileName);
    }
}
