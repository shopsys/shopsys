<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;

final class FilterRuleTest extends TestCase
{
    public function testParamIsUniquePerRuleAndHintAndDeterministic(): void
    {
        $firstRule = new FilterRule(Operator::IS, 'foo', '0', 'advancedSearch_createdAt');
        $secondRule = new FilterRule(Operator::IS, 'bar', 'new_1', 'advancedSearch_createdAt');

        $this->assertSame('advancedSearch_createdAt_0_value', $firstRule->param());
        $this->assertSame($firstRule->param(), $firstRule->param());
        $this->assertNotSame($firstRule->param(), $secondRule->param());
        $this->assertNotSame($firstRule->param('from'), $firstRule->param('to'));
    }

    public function testParamContainsOnlyDqlSafeCharacters(): void
    {
        $rule = new FilterRule(Operator::IS, 'foo', 'new-1"', 'advancedSearch_my.filter');

        $this->assertMatchesRegularExpression('/^\w+$/', $rule->param());
    }

    public function testLikeValueEscapesSqlWildcardsAndTranslatesUserWildcards(): void
    {
        $rule = new FilterRule(Operator::CONTAINS, 'foo*b_r', '0', 'advancedSearch_name');

        $this->assertSame('%foo%b\_r%', $rule->getLikeValue());
    }

    public function testCollectionIteratesRulesAndCollectsErrorsByFormIndex(): void
    {
        $firstRule = new FilterRule(Operator::IS, '1', '0', 'advancedSearch_customerId');
        $secondRule = new FilterRule(Operator::IS, '2', 'new_0', 'advancedSearch_customerId');
        $ruleCollection = new FilterRuleCollection([$firstRule, $secondRule]);

        $ruleCollection->addRuleError($secondRule, 'Customer not found.');

        $this->assertCount(2, $ruleCollection);
        $this->assertSame([$firstRule, $secondRule], iterator_to_array($ruleCollection));
        $this->assertSame(['new_0' => ['Customer not found.']], $ruleCollection->getRuleErrors());
    }
}
