<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\FinalCompilerPassFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\FinalCompilerPassFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class FinalCompilerPassFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): FinalCompilerPassFixer
    {
        return new FinalCompilerPassFixer();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/simple-compiler-pass.php', __DIR__ . '/wrong/simple-compiler-pass.php'];

        yield [__DIR__ . '/fixed/fully-qualified.php', __DIR__ . '/wrong/fully-qualified.php'];

        yield [__DIR__ . '/fixed/with-alias.php', __DIR__ . '/wrong/with-alias.php'];

        yield [__DIR__ . '/fixed/multiple-interfaces.php', __DIR__ . '/wrong/multiple-interfaces.php'];

        yield [__DIR__ . '/fixed/extends-and-implements.php', __DIR__ . '/wrong/extends-and-implements.php'];

        yield [__DIR__ . '/correct/already-final.php'];

        yield [__DIR__ . '/correct/abstract-compiler-pass.php'];

        yield [__DIR__ . '/correct/not-compiler-pass.php'];
    }
}
