<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer;

use Override;
use Shopsys\CodingStandards\CsFixer\ExtendedClassNameResolverFixer;
use Tests\CodingStandards\Unit\CsFixer\AbstractFixerTestCase;

final class ExtendedClassNameResolverFixerTest extends AbstractFixerTestCase
{
    #[Override]
    protected function createFixerService(): ExtendedClassNameResolverFixer
    {
        $fixer = new ExtendedClassNameResolverFixer();
        $fixer->configure([
            'namespace_prefixes' => ['Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\\'],
            'excluded_namespace_prefixes' => [],
        ]);

        return $fixer;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getTestingFiles(): iterable
    {
        yield [__DIR__ . '/fixed/static_calls.php', __DIR__ . '/wrong/static_calls.php'];

        yield [__DIR__ . '/fixed/fully_qualified_without_imports.php', __DIR__ . '/wrong/fully_qualified_without_imports.php'];

        yield [__DIR__ . '/fixed/aliased_import.php', __DIR__ . '/wrong/aliased_import.php'];

        yield [__DIR__ . '/correct/same_class_call.php'];

        yield [__DIR__ . '/correct/already_resolved.php'];

        yield [__DIR__ . '/correct/global_namespace.php'];
    }
}
