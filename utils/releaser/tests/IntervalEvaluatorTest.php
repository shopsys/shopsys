<?php

declare(strict_types=1);

namespace Shopsys\Releaser\Tests;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\Releaser\IntervalEvaluator;

final class IntervalEvaluatorTest extends TestCase
{
    private IntervalEvaluator $intervalEvaluator;

    protected function setUp(): void
    {
        $this->intervalEvaluator = new IntervalEvaluator();
    }

    /**
     * @param string $version
     * @param bool $expected
     */
    #[DataProvider('provideData')]
    public function test(string $version, bool $expected): void
    {
        $this->assertSame($expected, $this->intervalEvaluator->isClosedInterval($version));
    }

    /**
     * @return \Iterator
     */
    public static function provideData(): Iterator
    {
        yield ['v1.1.1', true];

        yield ['v1.1.1|v6.2.1', true];

        yield ['v1.1.1 |    v6.2.1', true];

        yield ['>1.1,<1.2', true];

        yield ['>1.1 ,   <1.2', true];

        yield ['>1.1|<1.2', false];

        yield ['>1.1', false];

        yield ['>=1.1  || <1.4', false];

        yield ['<3.0||>=3.2.0,<3.2.2', false];

        yield ['<3.0 || >= 3.2.0 , < 3.2.2', false];
    }
}
