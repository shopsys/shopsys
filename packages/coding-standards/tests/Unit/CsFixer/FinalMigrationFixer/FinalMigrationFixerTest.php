<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\FinalMigrationFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\FinalMigrationFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class FinalMigrationFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): FinalMigrationFixer
    {
        return new FinalMigrationFixer();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/simple-migration.php', __DIR__ . '/wrong/simple-migration.php'];

        yield [__DIR__ . '/fixed/fully-qualified.php', __DIR__ . '/wrong/fully-qualified.php'];

        yield [__DIR__ . '/correct/already-final.php'];

        yield [__DIR__ . '/correct/abstract-migration.php'];

        yield [__DIR__ . '/correct/not-migration.php'];
    }
}
