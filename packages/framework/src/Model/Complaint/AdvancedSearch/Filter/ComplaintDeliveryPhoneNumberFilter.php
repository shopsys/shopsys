<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter;

use Override;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneNumberSearchHelper;

class ComplaintDeliveryPhoneNumberFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'deliveryTelephone';

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
        return t('Delivery phone number');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getFieldName(): string
    {
        return 'deliveryTelephone';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDqlFieldExpression(): string
    {
        return PhoneNumberSearchHelper::getDqlExpression('cmp', 'deliveryTelephone');
    }
}
