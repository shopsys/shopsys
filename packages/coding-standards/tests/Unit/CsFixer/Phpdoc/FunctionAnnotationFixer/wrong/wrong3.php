<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

final class FunctionAnnotationFixerTestClass3
{
    /**
     * Tries to match names against use imports, e.g. "SomeClass" returns "SomeNamespace\SomeClass" for:
     *
     * use SomeNamespace\AnotherClass;
     * use SomeNamespace\SomeClass;
     */
    private function matchUseImports(Tokens $tokens, string $className): ?string
    {
    }
}
