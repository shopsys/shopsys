<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Array;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\ArrayAdapter;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpPathAccessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression\PhpValueComparator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompilerInterface;
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

        $arrayAdapter->getDatasource(new DatasourceRequest('id', []));

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

        $arrayAdapter->getDatasource(new DatasourceRequest('id', []));
        $this->assertSame(self::DATA, $capturedData);
    }

    public function testCapabilitiesAreThoseOfTheBuilder(): void
    {
        $capturedData = null;
        $arrayAdapter = $this->createArrayAdapter($capturedData);

        $this->assertTrue($arrayAdapter->getExpressionCapabilities()->supportsOperator(ExpressionOperatorEnum::IS_EMPTY));
        $this->assertFalse($arrayAdapter->getExpressionCapabilities()->supportsOperator('somethingElse'));
    }

    public function testExpressionOfAnotherMediumIsRefused(): void
    {
        $capturedData = null;
        $expressionCompilerStub = $this->createStub(ExpressionCompilerInterface::class);
        $expressionCompilerStub->method('compile')->willReturn(new DateTimeImmutable());
        $arrayAdapter = $this->createArrayAdapter($capturedData, $expressionCompilerStub);

        $this->expectException(ExpressionFromForeignMediumException::class);

        $arrayAdapter->getDatasource(new DatasourceRequest('id', [], Condition::equals('id', 1)));
    }

    private function createArrayAdapter(
        mixed &$capturedData,
        ?ExpressionCompilerInterface $expressionCompiler = null,
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
            new PhpExpressionBuilder(new PhpPathAccessor(), new PhpValueComparator()),
            $expressionCompiler ?? new ExpressionCompiler(new ExpressionOperatorEnum()),
        );
    }
}
