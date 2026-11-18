<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use Closure;
use DateTimeZone;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class GuessedFilterTest extends TestCase
{
    /**
     * An extension may change the field after the datagrid added the filter of it.
     */
    public function testNameLabelAndPathAreThoseOfTheFieldWhenAskedFor(): void
    {
        $field = new FieldDescriptor('author.fullName', [
            'label' => 'Author',
            'filterable' => true,
        ]);
        $filter = new GuessedFilter($field);

        $field->update([
            'label' => 'Writer',
            'property' => 'writer.fullName',
        ]);

        $this->assertSame('author_fullName', $filter->getName());
        $this->assertSame('Writer', $filter->getLabel());
        $this->assertSame('writer.fullName', $filter->getPath());
    }

    /**
     * @return iterable<string, array{\Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter, \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment): mixed}>
     */
    public static function getMethodsNeedingThePickedFilter(): iterable
    {
        yield 'operators' => [
            static fn (GuessedFilter $filter) => $filter->getOperators(),
        ];

        yield 'label of an operator' => [
            static fn (GuessedFilter $filter) => $filter->getOperatorLabel(ExpressionOperatorEnum::EQUALS),
        ];

        yield 'arity of the value' => [
            static fn (GuessedFilter $filter) => $filter->getValueArity(ExpressionOperatorEnum::EQUALS),
        ];

        yield 'form type of the value' => [
            static fn (GuessedFilter $filter) => $filter->getValueFormType(ExpressionValueArityEnum::SINGLE),
        ];

        yield 'form options of the value' => [
            static fn (GuessedFilter $filter) => $filter->getValueFormOptions(ExpressionValueArityEnum::SINGLE),
        ];

        yield 'adapting to the adapter' => [
            static fn (GuessedFilter $filter, FilterEnvironment $environment) => $filter->resolveFor($environment),
        ];

        yield 'condition of a rule' => [
            static fn (GuessedFilter $filter) => $filter->buildCondition(new FilterRuleData()),
        ];
    }

    /**
     * The filter of the field is picked only when the state of the datagrid is created, so it is configured
     * by a filter declared under the name of the field instead.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter, \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment): mixed $callMethod
     */
    #[DataProvider('getMethodsNeedingThePickedFilter')]
    public function testMethodNeedingThePickedFilterIsRefused(Closure $callMethod): void
    {
        $environment = new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new DateTimeZone('UTC'),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('filters()->add()');

        $callMethod(new GuessedFilter(new FieldDescriptor('name', ['filterable' => true])), $environment);
    }
}
