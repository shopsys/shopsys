<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter;

use Override;

class ComplaintDeliveryLastNameFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'deliveryLastName';

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
        return t('Delivery last name');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getFieldName(): string
    {
        return 'deliveryLastName';
    }
}
