<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter;

use Override;

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
    protected function getFieldName(): string
    {
        return 'deliveryTelephone';
    }
}
