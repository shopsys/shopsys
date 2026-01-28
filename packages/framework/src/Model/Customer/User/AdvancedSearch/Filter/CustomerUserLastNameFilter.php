<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserLastNameFilter extends AbstractCustomerUserContainsFilter
{
    public const string NAME = 'customerUserLastName';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return t('Last name');
    }

    #[Override]
    protected function getFieldName(): string
    {
        return 'lastName';
    }
}
