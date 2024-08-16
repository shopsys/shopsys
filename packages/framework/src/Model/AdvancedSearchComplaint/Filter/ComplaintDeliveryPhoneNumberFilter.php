<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter;

class ComplaintDeliveryPhoneNumberFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'deliveryTelephone';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    protected function getFieldName(): string
    {
        return 'deliveryTelephone';
    }
}
