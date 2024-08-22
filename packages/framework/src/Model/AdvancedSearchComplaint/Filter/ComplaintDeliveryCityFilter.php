<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter;

class ComplaintDeliveryCityFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'deliveryCity';

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
        return 'deliveryCity';
    }
}
