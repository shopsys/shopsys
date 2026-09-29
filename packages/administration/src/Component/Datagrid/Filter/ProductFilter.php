<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Form\ProductType;

/**
 * A filter over a related product picked in the product picker window instead of a select — the catalogue
 * is too large for a select. `ProductFilter::new('product')`.
 */
class ProductFilter extends EntityFilter
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS,
            ExpressionOperatorEnum::NOT_EQUALS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(string $operator): string
    {
        return ProductType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultValueFormOptions(string $operator): array
    {
        return [
            'enable_remove' => true,
        ];
    }
}
