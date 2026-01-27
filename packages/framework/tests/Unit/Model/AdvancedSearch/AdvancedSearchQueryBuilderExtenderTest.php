<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;

class AdvancedSearchQueryBuilderExtenderTest extends TestCase
{
    public function testExtendByAdvancedSearchData(): void
    {
        $ruleData = new AdvancedSearchRuleData();
        $ruleData->subject = 'testSubject';
        $ruleData->operator = 'testOperator';
        $ruleData->value = 'testValue';

        $advancedSearchData = [
            RuleFormViewDataFactory::TEMPLATE_RULE_FORM_KEY => null,
            0 => $ruleData,
        ];

        $filterMock = $this->createMock(AdvancedSearchFilterInterface::class);
        $filterMock
            ->expects($this->once())
            ->method('extendQueryBuilder')
            ->with(
                $this->isInstanceOf(QueryBuilder::class),
                $this->equalTo([$ruleData]),
            );

        $registryMock = $this->createMock(AdvancedSearchFilterRegistry::class);
        $registryMock
            ->expects($this->once())
            ->method('getFilter')
            ->with('product', 'testSubject')
            ->willReturn($filterMock);

        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $extender = new AdvancedSearchQueryBuilderExtender($registryMock);
        $extender->extendByAdvancedSearchData($queryBuilderMock, $advancedSearchData, 'product');
    }
}
