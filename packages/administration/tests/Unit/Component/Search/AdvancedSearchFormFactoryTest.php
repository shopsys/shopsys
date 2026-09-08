<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\AdvancedSearchFormFactory;
use Shopsys\AdministrationBundle\Component\Search\Filter;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class AdvancedSearchFormFactoryTest extends TestCase
{
    use SetTranslatorTrait;

    private AdvancedSearchFormFactory $advancedSearchFormFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->setTranslator();
        $this->advancedSearchFormFactory = new AdvancedSearchFormFactory($this->createFormFactory());
    }

    public function testFormContainsDefaultRuleAndTemplateWhenRequestHasNoRules(): void
    {
        $searchConfig = $this->createSearchConfig();

        $form = $this->advancedSearchFormFactory->createForm($searchConfig, Request::create('/'));

        $this->assertSame([0, AdvancedSearchFormFactory::TEMPLATE_RULE_KEY], array_keys($form->all()));
        $this->assertSame('name', $form->get('0')->get('subject')->getData());
        $this->assertNull($form->get('0')->get('operator')->getData());
    }

    public function testFormIsBuiltFromRequestRules(): void
    {
        $searchConfig = $this->createSearchConfig();
        $request = Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER => [
                '0' => ['subject' => 'name', 'operator' => 'contains', 'value' => 'foo'],
                'new_0' => ['subject' => 'city', 'operator' => 'notContains', 'value' => 'bar'],
            ],
        ]);

        $form = $this->advancedSearchFormFactory->createForm($searchConfig, $request);

        $this->assertSame([0, 'new_0', AdvancedSearchFormFactory::TEMPLATE_RULE_KEY], array_keys($form->all()));
        $formData = $form->getData();
        $this->assertSame('contains', $formData['0']['operator']);
        $this->assertSame('foo', $formData['0']['value']);
        $this->assertSame('city', $formData['new_0']['subject']);
    }

    public function testRuleWithUnknownSubjectIsIgnored(): void
    {
        $searchConfig = $this->createSearchConfig();
        $request = Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER => [
                '0' => ['subject' => 'unknown', 'operator' => 'contains', 'value' => 'foo'],
            ],
        ]);

        $form = $this->advancedSearchFormFactory->createForm($searchConfig, $request);

        $this->assertSame([0, AdvancedSearchFormFactory::TEMPLATE_RULE_KEY], array_keys($form->all()));
        $this->assertSame('name', $form->get('0')->get('subject')->getData());
    }

    public function testRuleWithArraySubjectIsIgnored(): void
    {
        $searchConfig = $this->createSearchConfig();
        $request = Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER => [
                '0' => ['subject' => ['name'], 'operator' => 'contains', 'value' => 'foo'],
            ],
        ]);

        $form = $this->advancedSearchFormFactory->createForm($searchConfig, $request);

        $this->assertSame([0, AdvancedSearchFormFactory::TEMPLATE_RULE_KEY], array_keys($form->all()));
        $this->assertSame('name', $form->get('0')->get('subject')->getData());
    }

    public function testRuleKeyWithIllegalCharactersIsSanitizedToAValidFormName(): void
    {
        $searchConfig = $this->createSearchConfig();
        $request = Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER => [
                'a b"' => ['subject' => 'name', 'operator' => 'contains', 'value' => 'foo'],
            ],
        ]);

        $form = $this->advancedSearchFormFactory->createForm($searchConfig, $request);

        $this->assertSame(['a_b_', AdvancedSearchFormFactory::TEMPLATE_RULE_KEY], array_keys($form->all()));
        $this->assertSame('foo', $form->get('a_b_')->get('value')->getData());
    }

    public function testIsSubmittedDetectsRulesOrResetFlag(): void
    {
        $this->assertFalse($this->advancedSearchFormFactory->isSubmitted(Request::create('/')));
        $this->assertTrue($this->advancedSearchFormFactory->isSubmitted(Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_RULES_QUERY_PARAMETER => [],
        ])));
        $this->assertTrue($this->advancedSearchFormFactory->isSubmitted(Request::create('/', 'GET', [
            SearchConfig::ADVANCED_SEARCH_FLAG_QUERY_PARAMETER => '1',
        ])));
    }

    public function testRuleFormViewKeepsRulesFormNamePrefixAndPreselectsTheSubject(): void
    {
        $searchConfig = $this->createSearchConfig();

        $ruleFormView = $this->advancedSearchFormFactory->createRuleFormView($searchConfig, 'city', 'new_0');

        $this->assertSame('f[new_0][subject]', $ruleFormView->children['subject']->vars['full_name']);
        $this->assertSame('city', $ruleFormView->children['subject']->vars['value']);
        $this->assertSame(['subject', 'operator', 'value'], array_keys($ruleFormView->children));
    }

    public function testRuleFormViewSanitizesIndex(): void
    {
        $searchConfig = $this->createSearchConfig();

        $ruleFormView = $this->advancedSearchFormFactory->createRuleFormView($searchConfig, 'city', 'new-0"');

        $this->assertSame('f[new_0_][subject]', $ruleFormView->children['subject']->vars['full_name']);
    }

    private function createSearchConfig(): SearchConfig
    {
        $searchConfig = new SearchConfig();
        $searchConfig->addFilter(Filter::create('name', 'Name'));
        $searchConfig->addFilter(Filter::create('city', 'City')->withOperators(Operator::CONTAINS, Operator::NOT_CONTAINS));

        return $searchConfig;
    }

    private function createFormFactory(): FormFactoryInterface
    {
        return Forms::createFormFactoryBuilder()
            ->addExtension(new CsrfExtension(new CsrfTokenManager()))
            ->getFormFactory();
    }
}
