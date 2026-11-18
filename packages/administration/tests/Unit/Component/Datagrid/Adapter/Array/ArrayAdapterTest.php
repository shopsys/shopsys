<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Array;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapter;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition\DqlCondition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSource;
use Shopsys\FrameworkBundle\Component\Grid\ArrayWithPaginationDataSourceFactory;

final class ArrayAdapterTest extends TestCase
{
    private const array DATA = [
        ['id' => 1, 'name' => 'Hrnek', 'domainId' => 1],
        ['id' => 2, 'name' => 'Talíř', 'domainId' => 2],
        ['id' => 3, 'name' => 'Hrnek velký', 'domainId' => 2],
    ];

    public function testDatasourceIsCreatedFromTheWholeDataWithoutCondition(): void
    {
        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData);

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], null));

        $this->assertSame(self::DATA, $capturedData);
    }

    public function testConditionNarrowsTheData(): void
    {
        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData);

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], Condition::contains('name', 'hrnek')));

        $this->assertSame([1, 3], array_column($capturedData, 'id'));
    }

    public function testComposedConditionNarrowsTheData(): void
    {
        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData);

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], Condition::andX(
            Condition::contains('name', 'hrnek'),
            Condition::in('domainId', [2]),
        )));

        $this->assertSame([3], array_column($capturedData, 'id'));
    }

    /**
     * The adapter keeps no state between the requests — a condition of one listing never leaks into another.
     */
    public function testEveryRequestIsAnsweredOnItsOwn(): void
    {
        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData);

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], Condition::in('domainId', [1])));
        $this->assertSame([1], array_column($capturedData, 'id'));

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], null));
        $this->assertSame(self::DATA, $capturedData);
    }

    public function testDqlConditionIsRefused(): void
    {
        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData);
        $dqlCondition = new DqlCondition(static fn (ProxyQuery $proxyQuery): string => 'SIZE(o.tags) > 3');

        $this->expectException(UnsupportedConditionException::class);
        $this->expectExceptionMessage(sprintf('Condition "%s" cannot be compiled', DqlCondition::class));

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], $dqlCondition));
    }

    public function testDataAreNarrowedByTheGivenBuilder(): void
    {
        $expressionBuilderStub = $this->createStub(ExpressionBuilderInterface::class);
        $expressionBuilderStub->method('compile')->willReturn(static fn (array $row): bool => $row['id'] === 2);

        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData, $expressionBuilderStub);
        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], Condition::equals('id', 1)));

        $this->assertSame([2], array_column($capturedData, 'id'));
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<\Closure>|null $expressionBuilder
     */
    private function createArrayAdapter(
        mixed &$capturedData,
        ?ExpressionBuilderInterface $expressionBuilder = null,
    ): ArrayAdapter {
        $dataSourceFactoryStub = $this->createStub(ArrayWithPaginationDataSourceFactory::class);
        $dataSourceFactoryStub->method('create')->willReturnCallback(
            function (array $data) use (&$capturedData): ArrayWithPaginationDataSource {
                $capturedData = $data;

                return $this->createStub(ArrayWithPaginationDataSource::class);
            },
        );

        return new ArrayAdapter(
            self::DATA,
            $dataSourceFactoryStub,
            $expressionBuilder ?? new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator(), new ExpressionOperatorEnum()),
        );
    }
}
