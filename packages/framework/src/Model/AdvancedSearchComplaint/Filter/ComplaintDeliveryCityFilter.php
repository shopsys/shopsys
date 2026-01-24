<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter;

use Override;

class ComplaintDeliveryCityFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'deliveryCity';

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    protected function getFieldName(): string
    {
        return 'deliveryCity';
    }
}
