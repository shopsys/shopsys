<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter;

use Override;

class ComplaintNumberFilter extends AbstractComplaintContainsFilter
{
    public const string NAME = 'number';

    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    #[Override]
    protected function getFieldName(): string
    {
        return 'number';
    }
}
