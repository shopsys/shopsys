<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Sniffs\ConstantVisibilityRequiredSniff;

use Shopsys\CodingStandards\Sniffs\ConstantVisibilityRequiredSniff;
use Tests\CodingStandards\Unit\Sniffs\AbstractSniffTestCase;

final class ConstantVisibilityRequiredSniffTest extends AbstractSniffTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getSniffClassName(): string
    {
        return ConstantVisibilityRequiredSniff::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getWrongFiles(): iterable
    {
        yield [__DIR__ . '/wrong/SingleValue.php'];
        yield [__DIR__ . '/wrong/MissingAnnotation.php'];
        yield [__DIR__ . '/wrong/Mixed.php'];
        yield [__DIR__ . '/wrong/MixedAtTheEnd.php'];
        yield [__DIR__ . '/wrong/MixedInTheMiddle.php'];
        yield [__DIR__ . '/wrong/SingleValueAfterMethodWithoutNamespace.php'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCorrectFiles(): iterable
    {
        yield [__DIR__ . '/correct/Annotation.php'];
        yield [__DIR__ . '/correct/Mixed.php'];
        yield [__DIR__ . '/correct/MixedVisibilities.php'];
        yield [__DIR__ . '/correct/MultipleValues.php'];
        yield [__DIR__ . '/correct/noClass.php'];
        yield [__DIR__ . '/correct/OutsideClass.php'];
        yield [__DIR__ . '/correct/SingleValueAfterMethodWithoutNamespace.php'];
        yield [__DIR__ . '/correct/SingleValueWithoutNamespace.php'];
    }
}
