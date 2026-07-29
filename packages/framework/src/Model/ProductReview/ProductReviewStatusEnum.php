<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductReviewStatusEnum extends AbstractEnum
{
    public const string STATUS_PENDING = 'pending';
    public const string STATUS_APPROVED = 'approved';
    public const string STATUS_REJECTED = 'rejected';
}
