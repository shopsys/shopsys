<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter\TwigComponent;

use DateTimeZone;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TwigComponent\FiltersComponent;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class FiltersComponentTest extends TestCase
{
    use SetTranslatorTrait;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
    }

    /**
     * The rules are rendered from the form submitted while the state was created, so its errors are shown.
     */
    public function testSubmittedFormIsRenderedWithItsErrors(): void
    {
        $filterFormFactory = new FilterFormFactory(DatagridStateFactoryFixture::createFormFactory());
        $filters = $this->createFilters();
        $form = $filterFormFactory->createForm($filters, 'Product_filter', [
            'groups' => [['rules' => [['filter' => 'price', 'operator' => 'contains', 'value' => 'x']]]],
        ]);

        $component = new FiltersComponent($filterFormFactory);
        $component->filter = new FilterPanel($form, $filters);

        $this->assertTrue($component->filter->isActive());
        $this->assertFalse($component->getFormView()->vars['valid']);
    }

    /**
     * The page swaps the prototypes into the rules of the form, so they are named as that form.
     */
    public function testPrototypesAreNamedAsTheFilterForm(): void
    {
        $filterFormFactory = new FilterFormFactory(DatagridStateFactoryFixture::createFormFactory());
        $filters = $this->createFilters();

        $component = new FiltersComponent($filterFormFactory);
        $component->filter = new FilterPanel($filterFormFactory->createForm($filters, 'Product_filter', null), $filters);

        $this->assertFalse($component->filter->isActive());
        $this->assertSame('Product_filter', $component->getPrototypesFormView()->vars['full_name']);
    }

    private function createFilters(): FilterCollection
    {
        $filter = NumericFilter::new('price');
        $filter->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        ));

        return new FilterCollection()->add($filter);
    }
}
