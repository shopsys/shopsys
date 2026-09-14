<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression\DqlExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathWalker;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\AbstractFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\DateFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\EntityFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Form\FormInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyEntityManagerFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Adapter\Orm\Fixture\DummyProduct;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * The filters over the paths of a real mapping, down to the query on the PostgreSQL platform — a value a filter
 * makes has to be one its column takes, whatever the column is.
 */
final class FilterQueryTest extends TestCase
{
    private const string DISPLAY_TIME_ZONE = 'Europe/Prague';

    private EntityManager $entityManager;

    private PathWalker $pathWalker;

    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);

        $this->entityManager = DummyEntityManagerFactory::create();
        $this->pathWalker = new PathWalker($this->entityManager);
    }

    /**
     * A mutable date and time takes a mutable date only, and reads the stored time in the default time zone.
     */
    public function testDayIsComparedByTheMomentsTheColumnTakes(): void
    {
        $queryBuilder = $this->createFilteredQuery(DateFilter::new('updatedAt'), ExpressionOperatorEnum::EQUALS, $this->day('2026-05-15'));

        $from = $queryBuilder->getParameter('dgexpr_0')->getValue();
        $to = $queryBuilder->getParameter('dgexpr_1')->getValue();
        $this->assertInstanceOf(DateTime::class, $from);
        $this->assertInstanceOf(DateTime::class, $to);
        $this->assertSame(date_default_timezone_get(), $from->getTimezone()->getName());
        $this->assertEquals($this->day('2026-05-15'), $from);
        $this->assertEquals(new DateTimeImmutable('2026-05-15 23:59:59.999999', new DateTimeZone(self::DISPLAY_TIME_ZONE)), $to);
        $this->assertStringContainsString('BETWEEN', $queryBuilder->getQuery()->getSQL());
    }

    public function testDayWithoutTimeIsComparedAsTheSameDay(): void
    {
        $queryBuilder = $this->createFilteredQuery(DateFilter::new('releasedOn'), ExpressionOperatorEnum::EQUALS, $this->day('2026-05-15'));

        $day = $queryBuilder->getParameter('dgexpr_0')->getValue();
        $this->assertInstanceOf(DateTime::class, $day);
        $this->assertSame('2026-05-15', $day->format('Y-m-d'));
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testYesOrNoIsBoundAsBoolean(): void
    {
        $queryBuilder = $this->createFilteredQuery(BooleanFilter::new('isActive'), BooleanFilter::OPERATOR_NO, null);

        $this->assertFalse($queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertSame('boolean', $queryBuilder->getParameter('dgexpr_0')->getType());
        $this->assertStringContainsString('SELECT', $queryBuilder->getQuery()->getSQL());
    }

    public function testRelatedEntitiesAreComparedInSubqueryOfTheCollection(): void
    {
        $queryBuilder = $this->createFilteredQuery(EntityFilter::new('tags'), ExpressionOperatorEnum::IN, [new TestEntity(3)]);

        $this->assertStringContainsString('dgsub_0_tags_join.id IN (:dgexpr_0)', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame(3, $queryBuilder->getParameter('dgexpr_0')->getValue());
        $this->assertStringContainsString('EXISTS', $queryBuilder->getQuery()->getSQL());
    }

    /**
     * The query would fail on an integer beyond the range of its column, so the form refuses it with a reason.
     */
    public function testIntegerBeyondTheRangeOfItsColumnIsInvalid(): void
    {
        $this->assertTrue($this->submitRatingFilter('32767')->isValid());
        $this->assertFalse($this->submitRatingFilter('32768')->isValid());
    }

    /**
     * @return \Symfony\Component\Form\FormInterface<\Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData>
     */
    private function submitRatingFilter(string $value): FormInterface
    {
        $filter = NumericFilter::new('rating');
        $filter->resolveFor($this->createEnvironment());
        $filters = new FilterCollection();
        $filters->add($filter);

        $form = DatagridNarrowingResolverFactory::createFormFactory()->createNamed('Product_filter', DatagridFilterFormType::class, FilterFormData::createStartingPoint(), [
            'filters' => $filters,
        ]);
        $form->submit(['groups' => [['rules' => [['filter' => 'rating', 'operator' => ExpressionOperatorEnum::GREATER_THAN, 'value' => $value]]]]]);

        return $form;
    }

    private function createFilteredQuery(AbstractFilter $filter, string $operator, mixed $value): QueryBuilder
    {
        $filter->resolveFor($this->createEnvironment());

        $rule = new FilterRuleData();
        $rule->filter = $filter->getName();
        $rule->operator = $operator;
        $rule->value = $value;

        $condition = $filter->buildCondition($rule);
        $this->assertNotNull($condition);

        $proxyQuery = new ProxyQuery(DummyProduct::class, $this->entityManager, $this->pathWalker, 'cs');
        $proxyQuery->addSelect('id');
        $proxyQuery->applyExpression((new DqlExpressionBuilder($proxyQuery, new ExpressionOperatorEnum()))->compile($condition));

        return $proxyQuery->getQueryBuilder();
    }

    private function createEnvironment(): FilterEnvironment
    {
        $adapter = new class($this->pathWalker) implements PathDescribingAdapterInterface {
            public function __construct(
                private readonly PathWalker $pathWalker,
            ) {
            }

            #[Override]
            public function describePath(string $path): PathDescription
            {
                return $this->pathWalker->walk(DummyProduct::class, $path)->description;
            }

            #[Override]
            public function getDatasource(DatasourceRequest $request): DataSourceInterface
            {
                throw new LogicException('The filters never ask for the records.');
            }
        };

        return new FilterEnvironment($adapter, new ExpressionOperatorEnum(), new DateTimeZone(self::DISPLAY_TIME_ZONE));
    }

    /**
     * Midnight of the day in the time zone of the administration, the way the date picker gives it.
     */
    private function day(string $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date . ' 00:00:00', new DateTimeZone(self::DISPLAY_TIME_ZONE));
    }
}
