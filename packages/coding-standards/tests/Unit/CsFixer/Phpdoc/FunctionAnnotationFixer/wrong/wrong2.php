<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer\Phpdoc;

final class FunctionAnnotationFixerTestClass2
{
    private function shouldSkip(string $type, ?Token $docToken): bool
    {
        if (!$type || $type === 'void') {
            return true;
        }

        return $docToken && Strings::contains($docToken->getContent(), '@return');
    }
}
