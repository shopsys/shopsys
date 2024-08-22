<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter;

class ComplaintDeliveryStreetFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'deliveryStreet';

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFieldName(): string
    {
        return 'deliveryStreet';
    }
}
