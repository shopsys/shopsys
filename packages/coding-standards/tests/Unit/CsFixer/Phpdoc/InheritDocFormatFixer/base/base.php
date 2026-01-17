<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\InheritDocFormatFixer;

class InheritDocFormatBaseClass
{
    public function methodOne(int $param): void
    {
    }

    public function methodTwo(int $param): void
    {
    }

    public function methodThree(): string
    {
        return 'string';
    }

    public function methodFour(int $param, string $param2): string
    {
        return 'string';
    }
}
