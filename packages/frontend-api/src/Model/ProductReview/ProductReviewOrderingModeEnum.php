<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductReviewOrderingModeEnum extends AbstractEnum
{
    public const string NEWEST = 'newest';
    public const string HIGHEST_RATING = 'highest_rating';
    public const string LOWEST_RATING = 'lowest_rating';
}
