<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Exception\FilterNotConfiguredException;
use Shopsys\AdministrationBundle\Component\Search\Filter;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class FilterTest extends TestCase
{
    public function testFilterDefaults(): void
    {
        $filter = Filter::create('city', 'City');

        $this->assertSame('city', $filter->getName());
        $this->assertSame('City', $filter->getLabel());
        $this->assertSame([Operator::CONTAINS, Operator::NOT_CONTAINS], $filter->getAllowedOperators());
        $this->assertSame(TextType::class, $filter->getValueFormType());
        $this->assertSame([], $filter->getValueFormOptions());
    }

    public function testFluentConfigurationIsStored(): void
    {
        $filter = Filter::create('price', 'Price')
            ->withOperators(Operator::GT, Operator::LT)
            ->withFormType(IntegerType::class, ['scale' => 0]);

        $this->assertSame([Operator::GT, Operator::LT], $filter->getAllowedOperators());
        $this->assertSame(IntegerType::class, $filter->getValueFormType());
        $this->assertSame(['scale' => 0], $filter->getValueFormOptions());
    }

    public function testApplyCallbackExtendsQueryBuilder(): void
    {
        $filter = Filter::create('city', 'City')
            ->apply(static function (QueryBuilder $queryBuilder, FilterRuleCollection $rules): void {
                foreach ($rules as $rule) {
                    $queryBuilder->andWhere(sprintf('o.city LIKE :%s', $rule->param()))
                        ->setParameter($rule->param(), $rule->getLikeValue());
                }
            });
        $queryBuilder = $this->createQueryBuilder();
        $rules = new FilterRuleCollection([new FilterRule(Operator::CONTAINS, 'Prague', '0', 'advancedSearch_city')]);

        $filter->extendQueryBuilder($queryBuilder, $rules);

        $this->assertSame('o.city LIKE :advancedSearch_city_0_value', (string)$queryBuilder->getDQLPart('where'));
        $this->assertSame('%Prague%', $queryBuilder->getParameter('advancedSearch_city_0_value')->getValue());
    }

    public function testExtendingQueryBuilderWithoutCallbackThrowsException(): void
    {
        $filter = Filter::create('city', 'City');

        $this->expectException(FilterNotConfiguredException::class);

        $filter->extendQueryBuilder($this->createQueryBuilder(), new FilterRuleCollection([]));
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->createStub(EntityManagerInterface::class));
    }
}
