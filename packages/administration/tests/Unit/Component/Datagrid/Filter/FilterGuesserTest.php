<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTimeZone;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\DateFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterGuesser;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\ProductFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class FilterGuesserTest extends TestCase
{
    /**
     * @return iterable<string, array{\Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription, class-string}>
     */
    public static function filtersByValueTypeProvider(): iterable
    {
        yield 'text' => [self::describe(PathValueTypeEnum::STRING), TextFilter::class];

        yield 'integer' => [self::describe(PathValueTypeEnum::INTEGER), NumericFilter::class];

        yield 'decimal' => [self::describe(PathValueTypeEnum::DECIMAL), NumericFilter::class];

        yield 'date' => [self::describe(PathValueTypeEnum::DATE), DateFilter::class];

        yield 'date and time' => [self::describe(PathValueTypeEnum::DATETIME), DateFilter::class];

        yield 'boolean' => [self::describe(PathValueTypeEnum::BOOLEAN), BooleanFilter::class];

        yield 'product' => [self::describe(PathValueTypeEnum::ASSOCIATION, Product::class), ProductFilter::class];
    }

    /**
     * @param class-string $expectedFilterClass
     */
    #[DataProvider('filtersByValueTypeProvider')]
    public function testFilterIsPickedByTheKindOfValueOfThePath(
        PathDescription $pathDescription,
        string $expectedFilterClass,
    ): void {
        $filter = (new FilterGuesser())->guess(new FieldDescriptor('value'), $this->createEnvironment(['value' => $pathDescription]));

        $this->assertSame($expectedFilterClass, $filter::class);
    }

    public function testFilterTakesTheNameTheLabelAndThePathOfTheField(): void
    {
        $field = new FieldDescriptor('authorName', ['label' => 'Author', 'property' => 'author.fullName']);

        $filter = (new FilterGuesser())->guess($field, $this->createEnvironment([
            'author.fullName' => self::describe(PathValueTypeEnum::STRING),
        ]));

        $this->assertSame('authorName', $filter->getName());
        $this->assertSame('Author', $filter->getLabel());
        $this->assertSame('author.fullName', $filter->getPath());
    }

    public function testDotsOfTheFieldNameAreReplacedInTheFilterName(): void
    {
        $filter = (new FilterGuesser())->guess(new FieldDescriptor('author.fullName'), $this->createEnvironment([
            'author.fullName' => self::describe(PathValueTypeEnum::STRING),
        ]));

        $this->assertSame('author_fullName', $filter->getName());
    }

    public function testVirtualFieldWithoutPropertyIsRefused(): void
    {
        $this->expectException(FieldNotFilterableException::class);
        $this->expectExceptionMessage('computed');

        (new FilterGuesser())->guess(new FieldDescriptor('computed', ['virtual' => true]), $this->createEnvironment([]));
    }

    public function testFieldCombiningSeveralPropertiesIsRefused(): void
    {
        $this->expectException(FieldNotFilterableException::class);
        $this->expectExceptionMessage('reviewer');

        (new FilterGuesser())->guess(new FieldDescriptor('reviewer', ['property' => ['lastName', 'firstName']]), $this->createEnvironment([]));
    }

    public function testValueWithoutKnownFilterIsRefused(): void
    {
        $this->expectException(FieldNotFilterableException::class);
        $this->expectExceptionMessage('MONEY');

        (new FilterGuesser())->guess(new FieldDescriptor('price'), $this->createEnvironment([
            'price' => self::describe(PathValueTypeEnum::MONEY),
        ]));
    }

    /**
     * A select of the related entities loads all of them whenever the list is displayed, which only the declaration
     * knows to be cheap.
     */
    public function testAssociationOtherThanProductIsRefused(): void
    {
        $this->expectException(FieldNotFilterableException::class);
        $this->expectExceptionMessage('declare an EntityFilter');

        (new FilterGuesser())->guess(new FieldDescriptor('value'), $this->createEnvironment([
            'value' => self::describe(PathValueTypeEnum::ASSOCIATION, TestEntity::class),
        ]));
    }

    public function testMissingPathIsRefused(): void
    {
        $this->expectException(FieldNotFilterableException::class);

        (new FilterGuesser())->guess(new FieldDescriptor('nope'), new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), [], false),
            new ExpressionOperatorEnum(),
            new DateTimeZone('UTC'),
        ));
    }

    public function testAdapterWithoutSchemaIsRefused(): void
    {
        $adapter = new class() implements AdapterInterface {
            public function getDatasource(DatasourceRequest $request): DataSourceInterface
            {
                throw new LogicException('The guesser never asks for the records.');
            }
        };

        $this->expectException(FieldNotFilterableException::class);

        (new FilterGuesser())->guess(new FieldDescriptor('name'), new FilterEnvironment($adapter, new ExpressionOperatorEnum(), new DateTimeZone('UTC')));
    }

    /**
     * @param class-string|null $targetEntityClass
     */
    private static function describe(PathValueTypeEnum $valueType, ?string $targetEntityClass = null): PathDescription
    {
        return new PathDescription('value', PathCardinalityEnum::TO_ONE, $valueType, $targetEntityClass);
    }

    /**
     * @param array<string, \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription> $pathDescriptions
     */
    private function createEnvironment(array $pathDescriptions): FilterEnvironment
    {
        return new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), $pathDescriptions, false),
            new ExpressionOperatorEnum(),
            new DateTimeZone('UTC'),
        );
    }
}
