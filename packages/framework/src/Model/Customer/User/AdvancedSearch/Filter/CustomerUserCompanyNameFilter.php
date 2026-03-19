<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserCompanyNameFilter extends AbstractCustomerUserCompanyContainsFilter
{
    public const string NAME = 'customerUserCompanyName';

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
        return t('Company name');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getFieldName(): string
    {
        return 'companyName';
    }
}
