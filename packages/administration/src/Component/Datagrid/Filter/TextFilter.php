<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * A filter over a text — `TextFilter::new('author.fullName', t('Author name'))`.
 */
class TextFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::CONTAINS,
            ExpressionOperatorEnum::STARTS_WITH,
            ExpressionOperatorEnum::ENDS_WITH,
            ExpressionOperatorEnum::EQUALS,
            ExpressionOperatorEnum::NOT_EQUALS,
            ExpressionOperatorEnum::IS_NULL,
            ExpressionOperatorEnum::IS_NOT_NULL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(string $operator): string
    {
        return TextType::class;
    }
}
