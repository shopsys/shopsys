<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\AdvancedSearchApplier;
use Shopsys\AdministrationBundle\Component\Search\AdvancedSearchFormFactory;
use Shopsys\AdministrationBundle\Component\Search\Filter;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class AdvancedSearchApplierTest extends TestCase
{
    use SetTranslatorTrait;

    private AdvancedSearchFormFactory $advancedSearchFormFactory;

    private AdvancedSearchApplier $advancedSearchApplier;

    /**
     * @var array<string, \Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection[]>
     */
    private array $appliedRuleCollectionsByFilterName = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->setTranslator();
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension(new CsrfTokenManager()))
            ->getFormFactory();
        $this->advancedSearchFormFactory = new AdvancedSearchFormFactory($formFactory);
        $this->advancedSearchApplier = new AdvancedSearchApplier();
    }

    public function testRulesAreGroupedBySubjectAndEachFilterIsCalledOnceWithAllItsRules(): void
    {
        $searchConfig = $this->createSearchConfig();
        $form = $this->createSubmittedForm($searchConfig, [
            '0' => ['subject' => 'name', 'operator' => 'contains', 'value' => 'foo'],
            'new_0' => ['subject' => 'name', 'operator' => 'notContains', 'value' => 'bar'],
            'new_1' => ['subject' => 'deleted', 'operator' => 'notSet', 'value' => null],
        ]);

        $this->advancedSearchApplier->apply($searchConfig, $this->createQueryBuilder(), $form);

        $this->assertCount(1, $this->appliedRuleCollectionsByFilterName['name']);
        $nameRules = $this->appliedRuleCollectionsByFilterName['name'][0]->getRules();
        $this->assertCount(2, $nameRules);
        $this->assertSame(Operator::CONTAINS, $nameRules[0]->operator);
        $this->assertSame('foo', $nameRules[0]->value);
        $this->assertSame(Operator::NOT_CONTAINS, $nameRules[1]->operator);

        $this->assertCount(1, $this->appliedRuleCollectionsByFilterName['deleted']);
        $this->assertCount(1, $this->appliedRuleCollectionsByFilterName['deleted'][0]->getRules());
    }

    public function testIncompleteAndDisallowedRulesAreSkipped(): void
    {
        $searchConfig = $this->createSearchConfig();
        $form = $this->createSubmittedForm($searchConfig, [
            '0' => ['subject' => 'name', 'operator' => null, 'value' => 'foo'],
            '1' => ['subject' => 'name', 'operator' => 'contains', 'value' => ''],
            '2' => ['subject' => 'name', 'operator' => 'notSet', 'value' => null],
        ]);

        $this->advancedSearchApplier->apply($searchConfig, $this->createQueryBuilder(), $form);

        $this->assertArrayNotHasKey('name', $this->appliedRuleCollectionsByFilterName);
        $this->assertArrayNotHasKey('deleted', $this->appliedRuleCollectionsByFilterName);
    }

    public function testValuelessOperatorRuleIsAppliedWithoutValue(): void
    {
        $searchConfig = $this->createSearchConfig();
        $form = $this->createSubmittedForm($searchConfig, [
            '0' => ['subject' => 'deleted', 'operator' => 'notSet', 'value' => null],
        ]);

        $this->advancedSearchApplier->apply($searchConfig, $this->createQueryBuilder(), $form);

        $this->assertCount(1, $this->appliedRuleCollectionsByFilterName['deleted'][0]->getRules());
    }

    public function testRuleErrorsAreAddedToTheValueFieldOfTheRuleRow(): void
    {
        $searchConfig = new SearchConfig();
        $searchConfig->addFilter(
            Filter::create('customerId', 'Customer ID')
                ->withOperators(Operator::IS)
                ->apply(static function (QueryBuilder $queryBuilder, FilterRuleCollection $rules): void {
                    foreach ($rules as $rule) {
                        $rules->addRuleError($rule, 'Customer not found.');
                    }
                }),
        );
        $form = $this->createSubmittedForm($searchConfig, [
            'new_0' => ['subject' => 'customerId', 'operator' => 'is', 'value' => '42'],
        ]);

        $this->advancedSearchApplier->apply($searchConfig, $this->createQueryBuilder(), $form);

        $errors = $form->get('new_0')->get('value')->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame('Customer not found.', $errors[0]->getMessage());
    }

    private function createSearchConfig(): SearchConfig
    {
        $recordCollection = function (string $filterName) {
            return function (QueryBuilder $queryBuilder, FilterRuleCollection $rules) use ($filterName): void {
                $this->appliedRuleCollectionsByFilterName[$filterName][] = $rules;
            };
        };

        $searchConfig = new SearchConfig();
        $searchConfig->addFilter(
            Filter::create('name', 'Name')->apply($recordCollection('name')),
        );
        $searchConfig->addFilter(
            Filter::create('deleted', 'Deleted')
                ->withOperators(Operator::NOT_SET)
                ->apply($recordCollection('deleted')),
        );

        return $searchConfig;
    }

    /**
     * @param array<string, array{subject: string, operator: string|null, value: mixed}> $rulesRequestData
     */
    private function createSubmittedForm(SearchConfig $searchConfig, array $rulesRequestData): FormInterface
    {
        $request = Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER => $rulesRequestData,
        ]);

        return $this->advancedSearchFormFactory->createForm($searchConfig, $request);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->createStub(EntityManagerInterface::class));
    }
}
