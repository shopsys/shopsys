<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserFirstNameFilter extends AbstractCustomerUserContainsFilter
{
    public const string NAME = 'customerUserFirstName';

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
        return t('First name');
    }

    #[Override]
    protected function getFieldName(): string
    {
        return 'firstName';
    }
}
