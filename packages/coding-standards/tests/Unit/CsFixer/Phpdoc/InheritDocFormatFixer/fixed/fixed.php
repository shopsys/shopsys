<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\InheritDocFormatFixer;

final class InheritDocFormatTestClass extends InheritDocFormatBaseClass
{
    /**
     * {@inheritdoc}
     */
    public function methodOne(int $param): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function methodTwo(int $param): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function methodThree(): string
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    public function methodFour(int $param, string $param2): string
    {
        return 'string';
    }
}
