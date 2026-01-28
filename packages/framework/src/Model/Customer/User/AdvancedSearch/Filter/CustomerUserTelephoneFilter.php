<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserTelephoneFilter extends AbstractCustomerUserContainsFilter
{
    public const string NAME = 'customerUserTelephone';

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
        return t('Telephone');
    }

    #[Override]
    protected function getFieldName(): string
    {
        return 'telephone';
    }
}
