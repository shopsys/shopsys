<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\FinalFormTypeFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\FinalFormTypeFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class FinalFormTypeFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): FinalFormTypeFixer
    {
        return new FinalFormTypeFixer();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        // Test cases with expected fixes (fixed/wrong pairs)
        yield [__DIR__ . '/fixed/simple-form-type.php', __DIR__ . '/wrong/simple-form-type.php'];

        yield [__DIR__ . '/fixed/form-extension.php', __DIR__ . '/wrong/form-extension.php'];

        yield [__DIR__ . '/fixed/fully-qualified.php', __DIR__ . '/wrong/fully-qualified.php'];

        yield [__DIR__ . '/fixed/with-alias.php', __DIR__ . '/wrong/with-alias.php'];

        yield [__DIR__ . '/fixed/namespaced.php', __DIR__ . '/wrong/namespaced.php'];

        // Test cases that should not be changed (correct files)
        yield [__DIR__ . '/correct/already-final.php'];

        yield [__DIR__ . '/correct/abstract-form.php'];

        yield [__DIR__ . '/correct/not-form-type.php'];
    }
}
