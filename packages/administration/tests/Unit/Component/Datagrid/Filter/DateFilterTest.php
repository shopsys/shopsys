<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeImmutable;
use DateTimeZone;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\DateFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DateFilterTest extends TestCase
{
    private const string DISPLAY_TIME_ZONE = 'Europe/Prague';

    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);
    }

    /**
     * The bounds are moments — a medium storing the time in another time zone converts them, see `DqlExpressionBuilderTest`.
     */
    public function testDayOverDateAndTimeIsTheRangeOfTheDayInTheTimeZoneOfTheAdministration(): void
    {
        $filter = $this->resolve(PathValueTypeEnum::DATETIME);

        $condition = $filter->buildCondition($this->createRule(ExpressionOperatorEnum::EQUALS, $this->day('2026-03-29')));

        // the DST change on 29 March 2026 makes the day 23 hours long
        $this->assertEquals(
            Condition::between(
                'createdAt',
                new DateTimeImmutable('2026-03-28 23:00:00', new DateTimeZone('UTC')),
                new DateTimeImmutable('2026-03-29 21:59:59.999999', new DateTimeZone('UTC')),
            ),
            $condition,
        );
    }

    /**
     * Over a collection every comparison is matched by a related row of its own, so a day compared by its two bounds
     * would match an order from the start of the month and another one from its end.
     */
    public function testDayOverCollectionIsSingleComparison(): void
    {
        $filter = $this->resolve(PathValueTypeEnum::DATETIME, PathCardinalityEnum::TO_MANY);

        $this->assertEquals(
            Condition::between(
                'createdAt',
                new DateTimeImmutable('2026-05-15 00:00:00', new DateTimeZone(self::DISPLAY_TIME_ZONE)),
                new DateTimeImmutable('2026-05-15 23:59:59.999999', new DateTimeZone(self::DISPLAY_TIME_ZONE)),
            ),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::EQUALS, $this->day('2026-05-15'))),
        );
    }

    public function testBeforeAndAfterAreExclusiveOfTheDay(): void
    {
        $filter = $this->resolve(PathValueTypeEnum::DATETIME);

        $this->assertEquals(
            Condition::lessThan('createdAt', new DateTimeImmutable('2026-05-11 22:00:00', new DateTimeZone('UTC'))),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::LESS_THAN, $this->day('2026-05-12'))),
        );
        $this->assertEquals(
            Condition::greaterThanOrEqual('createdAt', new DateTimeImmutable('2026-05-12 22:00:00', new DateTimeZone('UTC'))),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::GREATER_THAN, $this->day('2026-05-12'))),
        );
    }

    public function testBetweenIncludesBothDays(): void
    {
        $filter = $this->resolve(PathValueTypeEnum::DATETIME);

        $this->assertEquals(
            Condition::between(
                'createdAt',
                new DateTimeImmutable('2026-05-09 22:00:00', new DateTimeZone('UTC')),
                new DateTimeImmutable('2026-05-12 21:59:59.999999', new DateTimeZone('UTC')),
            ),
            $filter->buildCondition($this->createRule(ExpressionOperatorEnum::BETWEEN, [
                'from' => $this->day('2026-05-10'),
                'to' => $this->day('2026-05-12'),
            ])),
        );
    }

    public function testDateWithoutTimeIsComparedAsItIs(): void
    {
        $filter = $this->resolve(PathValueTypeEnum::DATE);
        $day = $this->day('2026-05-12');

        $this->assertEquals(Condition::equals('createdAt', $day), $filter->buildCondition($this->createRule(ExpressionOperatorEnum::EQUALS, $day)));
    }

    public function testPickedDayIsKeptInTheTimeZoneOfTheAdministration(): void
    {
        $filter = $this->resolve(PathValueTypeEnum::DATETIME);

        $this->assertSame(self::DISPLAY_TIME_ZONE, $filter->getValueFormOptions(ExpressionValueArityEnum::SINGLE)['model_timezone']);
        $this->assertSame('is before', $filter->getOperatorLabel(ExpressionOperatorEnum::LESS_THAN));
        $this->assertSame('is', $filter->getOperatorLabel(ExpressionOperatorEnum::EQUALS));
    }

    private function resolve(
        PathValueTypeEnum $valueType,
        PathCardinalityEnum $cardinality = PathCardinalityEnum::TO_ONE,
    ): DateFilter {
        $filter = DateFilter::new('createdAt');
        $filter->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), [
                'createdAt' => new PathDescription('createdAt', $cardinality, $valueType),
            ]),
            new ExpressionOperatorEnum(),
            new DateTimeZone(self::DISPLAY_TIME_ZONE),
        ));

        return $filter;
    }

    /**
     * Midnight of the day in the time zone of the administration, the way the date picker gives it.
     */
    private function day(string $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date . ' 00:00:00', new DateTimeZone(self::DISPLAY_TIME_ZONE));
    }

    private function createRule(string $operator, mixed $value): FilterRuleData
    {
        $rule = new FilterRuleData();
        $rule->operator = $operator;
        $rule->value = $value;

        return $rule;
    }
}
