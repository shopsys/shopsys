<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ConstantVisibilityRequiredSniff;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ConstantVisibilityRequiredSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getSniffClassName(): string
    {
        return ConstantVisibilityRequiredSniff::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/SingleValue.php'];

        yield [__DIR__ . '/wrong/MissingAnnotation.php'];

        yield [__DIR__ . '/wrong/Various.php'];

        yield [__DIR__ . '/wrong/MixedAtTheEnd.php'];

        yield [__DIR__ . '/wrong/MixedInTheMiddle.php'];

        yield [__DIR__ . '/wrong/SingleValueAfterMethodWithoutNamespace.php'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/correct/Annotation.php'];

        yield [__DIR__ . '/correct/Various.php'];

        yield [__DIR__ . '/correct/MixedVisibilities.php'];

        yield [__DIR__ . '/correct/MultipleValues.php'];

        yield [__DIR__ . '/correct/noClass.php'];

        yield [__DIR__ . '/correct/OutsideClass.php'];

        yield [__DIR__ . '/correct/SingleValueAfterMethodWithoutNamespace.php'];

        yield [__DIR__ . '/correct/SingleValueWithoutNamespace.php'];
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
