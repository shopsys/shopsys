<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter;

use Override;

class CustomerUserCompanyNumberFilter extends AbstractCustomerUserCompanyContainsFilter
{
    public const string NAME = 'customerUserCompanyNumber';

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
        return t('Company number');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getFieldName(): string
    {
        return 'companyNumber';
    }
}
