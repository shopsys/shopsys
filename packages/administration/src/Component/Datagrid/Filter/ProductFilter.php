<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\InvalidDatagridDeclarationException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\FrameworkBundle\Form\ProductType;
use Shopsys\FrameworkBundle\Model\Product\Product;

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
    public function getValueFormType(ExpressionValueArityEnum $arity): string
    {
        return ProductType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultValueFormOptions(ExpressionValueArityEnum $arity): array
    {
        return [
            'enable_remove' => true,
        ];
    }

    /**
     * The picked product is compared by its identifier with the path, so the path has to lead to a product.
     *
     * {@inheritdoc}
     */
    #[Override]
    protected function assertApplicable(): void
    {
        parent::assertApplicable();

        $entityClass = (string)$this->getEntityClass();

        if (is_a($entityClass, Product::class, true) === false) {
            throw InvalidDatagridDeclarationException::filterNotApplicable($this->getName(), sprintf('the path "%s" has to lead to a product, it leads to "%s".', $this->path, $entityClass));
        }
    }
}
