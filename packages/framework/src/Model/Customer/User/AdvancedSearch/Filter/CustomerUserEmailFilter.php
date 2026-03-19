<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserEmailFilter extends AbstractCustomerUserContainsFilter
{
    public const string NAME = 'customerUserEmail';

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
        return t('Email');
    }

    #[Override]
    protected function getFieldName(): string
    {
        return 'email';
    }
}
