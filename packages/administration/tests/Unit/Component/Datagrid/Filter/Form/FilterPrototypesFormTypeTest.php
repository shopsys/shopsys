<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter\Form;

use DateTimeZone;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterPrototypesFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRuleType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Form\FormInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

final class FilterPrototypesFormTypeTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);
    }

    /**
     * The page swaps the operation and the value of a rule from these, so every filter has one per arity of its
     * operations — in the order of the operations: equals ... (single), between (range), isNull (none).
     */
    public function testEveryFilterHasPrototypeForEveryArityOfItsOperations(): void
    {
        $prototypesForm = $this->createPrototypesForm();
        $view = $prototypesForm->createView();

        $this->assertSame(['single', 'range', 'none'], array_keys($prototypesForm->get('prototypes')->get('price')->all()));
        $this->assertSame(['none'], array_keys($prototypesForm->get('prototypes')->get('inStock')->all()));
        $this->assertArrayHasKey('value', $view['prototypes']['price']['range']->children);
        $this->assertArrayNotHasKey('value', $view['prototypes']['price']['none']->children);
        $this->assertSame('range', $view['prototypes']['price']['range']['operator']->vars['choices'][6]->attr['data-arity']);
    }

    /**
     * A prototype is a rule of its filter starting with the first operation of its arity, named as a rule of the
     * filter form apart from its path, which is what the page renames.
     */
    public function testPrototypeIsRuleStartingWithTheFirstOperationOfItsArity(): void
    {
        $prototypesForm = $this->createPrototypesForm();
        $view = $prototypesForm->createView();

        foreach (['single' => 'equals', 'range' => 'between', 'none' => 'isNull'] as $arity => $operator) {
            $prototype = $prototypesForm->get('prototypes')->get('price')->get($arity);

            $this->assertInstanceOf(FilterRuleType::class, $prototype->getConfig()->getType()->getInnerType());
            $this->assertSame('price', $prototype->get('filter')->getData());
            $this->assertSame($operator, $view['prototypes']['price'][$arity]['operator']->vars['value']);
        }

        $this->assertSame('Product_filter[prototypes][price][range][operator]', $view['prototypes']['price']['range']['operator']->vars['full_name']);
        $this->assertSame('Product_filter[prototypes][price][range][value][from]', $view['prototypes']['price']['range']['value']['from']->vars['full_name']);
    }

    private function createPrototypesForm(): FormInterface
    {
        $environment = new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        );
        $filters = new FilterCollection();

        foreach ([TextFilter::new('name', 'Name'), NumericFilter::new('price', 'Price'), BooleanFilter::new('inStock', 'In stock')] as $filter) {
            $filter->resolveFor($environment);
            $filters->add($filter);
        }

        return DatagridNarrowingResolverFactory::createFormFactory()->createNamed('Product_filter', FilterPrototypesFormType::class, null, [
            'filters' => $filters,
        ]);
    }
}
