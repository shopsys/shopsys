<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\OrmJoinColumnRequireNullableFixer;

use Shopsys\CodingStandards\CsFixer\OrmJoinColumnRequireNullableFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class OrmJoinColumnRequireNullableFixerTest extends AbstractFixerTestCase
{
    /**
     * @return \Shopsys\CodingStandards\CsFixer\OrmJoinColumnRequireNullableFixer
     */
    protected function createFixerService(): OrmJoinColumnRequireNullableFixer
    {
        return new OrmJoinColumnRequireNullableFixer();
    }

    /**
     * {@inheritdoc}
     */
    public function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/many_to_one_missing_join_column.php', __DIR__ . '/wrong/many_to_one_missing_join_column.php'];

        yield [__DIR__ . '/fixed/many_to_one_missing_nullable_param.php', __DIR__ . '/wrong/many_to_one_missing_nullable_param.php'];

        yield [__DIR__ . '/fixed/one_to_one_missing_join_column.php', __DIR__ . '/wrong/one_to_one_missing_join_column.php'];

        yield [__DIR__ . '/fixed/one_to_one_missing_nullable_param.php', __DIR__ . '/wrong/one_to_one_missing_nullable_param.php'];

        yield [__DIR__ . '/fixed/one_to_one_multiline_missing_nullable_param.php', __DIR__ . '/wrong/one_to_one_multiline_missing_nullable_param.php'];

        yield [__DIR__ . '/correct/one_to_many.php'];

        yield [__DIR__ . '/correct/many_to_one_missing_join_column.php'];

        yield [__DIR__ . '/correct/many_to_one_missing_nullable_param.php'];

        yield [__DIR__ . '/correct/one_to_one_missing_nullable_param.php'];
    }
}
