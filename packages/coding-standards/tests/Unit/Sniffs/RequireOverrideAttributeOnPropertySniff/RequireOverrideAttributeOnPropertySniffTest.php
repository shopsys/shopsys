<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\RequireOverrideAttributeOnPropertySniff;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\General\RequireOverrideAttributeOnPropertySniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class RequireOverrideAttributeOnPropertySniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getSniffClassName(): string
    {
        return RequireOverrideAttributeOnPropertySniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getFixableFiles(): iterable
    {
        yield [__DIR__ . '/Fixed/SimpleWithParentClass.php', __DIR__ . '/Wrong/SimpleWithParentClass.php'];

        yield [__DIR__ . '/Fixed/ClassWithInterface.php', __DIR__ . '/Wrong/ClassWithInterface.php'];

        yield [__DIR__ . '/Fixed/CombinedParentsClass.php', __DIR__ . '/Wrong/CombinedParentsClass.php'];

        yield [__DIR__ . '/Correct/NoOverrideNeeded.php'];

        yield [__DIR__ . '/Correct/AlreadyHasOverride.php'];

        yield [__DIR__ . '/Correct/PrivateParentProperty.php'];
    }

    #[DataProvider('getFixableFiles')]
    public function testFixableFiles(string $fixedFileName, ?string $inputFileName = null): void
    {
        $this->runFixableFilesTest($fixedFileName, $inputFileName);
    }
}
