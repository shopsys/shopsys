<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserCompanyTaxNumberFilter extends AbstractCustomerUserCompanyContainsFilter
{
    public const string NAME = 'customerUserCompanyTaxNumber';

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
        return t('Company Tax number');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getFieldName(): string
    {
        return 'companyTaxNumber';
    }
}
