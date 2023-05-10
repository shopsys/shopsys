<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\InheritDocFormatFixer;

class InheritDocFormatBaseClass
{
    /**
     * @param int $param
     */
    public function methodOne(int $param): void
    {
    }

    /**
     * @param int $param
     */
    public function methodTwo(int $param): void
    {
    }

    /**
     * @return string
     */
    public function methodThree(): string
    {
        return 'string';
    }

    /**
     * @param int $param
     * @param string $param2
     * @return string
     */
    public function methodFour(int $param, string $param2): string
    {
        return 'string';
    }
}
