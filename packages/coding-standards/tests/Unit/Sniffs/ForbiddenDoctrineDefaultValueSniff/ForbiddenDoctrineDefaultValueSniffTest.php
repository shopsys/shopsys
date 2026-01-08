<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ForbiddenDoctrineDefaultValueSniff;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ForbiddenDoctrineDefaultValueSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

class ForbiddenDoctrineDefaultValueSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getSniffClassName(): string
    {
        return ForbiddenDoctrineDefaultValueSniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/default_value_annotation.php'];

        yield [__DIR__ . '/wrong/different_order_annotation.php'];

        yield [__DIR__ . '/wrong/multiline_annotation.php'];

        yield [__DIR__ . '/wrong/spaces_around_annotation.php'];

        yield [__DIR__ . '/wrong/split_annotation.php'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/correct/missing_default_value_annotation.php'];

        yield [__DIR__ . '/correct/invalid_docblock_annotation.php'];
    }

    /**
     * @param string $fileToTest
     */
    #[DataProvider('getWrongFiles')]
    public function testWrongFiles(string $fileToTest): void
    {
        $this->runWrongFilesTest($fileToTest);
    }

    /**
     * @param string $fileToTest
     */
    #[DataProvider('getCorrectFiles')]
    public function testCorrectFiles(string $fileToTest): void
    {
        $this->runCorrectFilesTest($fileToTest);
    }
}
